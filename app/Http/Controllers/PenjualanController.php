<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\PenjualanDetailModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Penjualan',
            'list'  => ['Home', 'Penjualan']
        ];

        $page = (object) [
            'title' => 'Daftar Transaksi Penjualan yang sudah terdaftar dalam sistem'
        ];

        $activeMenu = 'penjualan';

        $user = UserModel::all();

        return view('penjualan.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'user' => $user,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        $penjualan = PenjualanModel::select(
            'penjualan_id',
            'user_id',
            'pembeli',
            'penjualan_kode',
            'penjualan_tanggal'
        )
            ->with('user')
            ->with('details');

        if ($request->user_id) {
            $penjualan->where('user_id', $request->user_id);
        }

        if ($request->start_date && $request->end_date) {
            $penjualan->whereBetween('penjualan_tanggal', [$request->start_date, $request->end_date]);
        }

        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_items', function ($penjualan) {
                return $penjualan->details->sum('jumlah');
            })
            ->addColumn('total_amount', function ($penjualan) {
                return $penjualan->getTotalAmount();
            })
            ->addColumn('aksi', function ($penjualan) {
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button>';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button>';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual', 'harga_beli')
            ->get();
        $user = UserModel::select('user_id', 'nama')->get();
        $kode = PenjualanModel::generateKode();

        return view('penjualan.create_ajax')
            ->with('barang', $barang)
            ->with('user', $user)
            ->with('kode', $kode);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|integer',
                'pembeli' => 'required|string|max:50',
                'penjualan_kode' => 'required|string|max:20|unique:t_penjualan,penjualan_kode',
                'penjualan_tanggal' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.barang_id' => 'required|integer|exists:m_barang,barang_id',
                'items.*.jumlah' => 'required|integer|min:1',
                'items.*.harga' => 'required|integer|min:0',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            DB::beginTransaction();
            try {
                // Simpan header penjualan
                $penjualan = PenjualanModel::create([
                    'user_id' => $request->user_id,
                    'pembeli' => $request->pembeli,
                    'penjualan_kode' => $request->penjualan_kode,
                    'penjualan_tanggal' => $request->penjualan_tanggal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Simpan detail penjualan tanpa sentuh stok
                foreach ($request->items as $item) {
                    PenjualanDetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga' => $item['harga'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Transaksi penjualan berhasil disimpan'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }
        return redirect('/');
    }


    public function show_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['user', 'details.barang'])->find($id);

        if (!$penjualan) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $total = $penjualan->getTotalAmount();

        return view('penjualan.show_ajax', [
            'penjualan' => $penjualan,
            'total' => $total
        ]);
    }

    public function edit_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['details.barang'])->find($id);

        if (!$penjualan) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $barang = BarangModel::select('barang_id', 'barang_nama', 'barang_kode', 'harga_jual', 'harga_beli')
            ->get();
        $user = UserModel::select('user_id', 'nama')->get();

        return view('penjualan.edit_ajax', [
            'penjualan' => $penjualan,
            'barang' => $barang,
            'user' => $user
        ]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|integer',
                'pembeli' => 'required|string|max:50',
                'penjualan_tanggal' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.barang_id' => 'required|integer|exists:m_barang,barang_id',
                'items.*.jumlah' => 'required|integer|min:1',
                'items.*.harga' => 'required|integer|min:0',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            DB::beginTransaction();
            try {
                $penjualan = PenjualanModel::find($id);

                if (!$penjualan) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data penjualan tidak ditemukan',
                    ]);
                }

                // Step 1: Update penjualan header
                $penjualan->update([
                    'user_id' => $request->user_id,
                    'pembeli' => $request->pembeli,
                    'penjualan_tanggal' => $request->penjualan_tanggal,
                    'updated_at' => now()
                ]);

                // Step 2: Hapus detail lama
                PenjualanDetailModel::where('penjualan_id', $id)->delete();

                // Step 3: Tambahkan detail baru
                foreach ($request->items as $item) {
                    PenjualanDetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga' => $item['harga'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data penjualan berhasil diperbarui'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }
        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['details.barang', 'user'])->find($id);

        if (!$penjualan) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return view('penjualan.confirm_ajax', [
            'penjualan' => $penjualan
        ]);
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            DB::beginTransaction();
            try {
                $penjualan = PenjualanModel::find($id);

                if (!$penjualan) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data penjualan tidak ditemukan',
                    ]);
                }

                // Step 1: Delete detail records first
                PenjualanDetailModel::where('penjualan_id', $id)->delete();

                // Step 2: Delete the main penjualan record
                $penjualan->delete();

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data penjualan berhasil dihapus'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }
        return redirect('/');
    }

    public function import()
    {
        return view('penjualan.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $validator = Validator::make($request->all(), [
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_stok');
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, false, true, true);

            $penjualanGrouped = [];

            // Proses dan kelompokkan berdasarkan penjualan_kode
            foreach ($data as $index => $row) {
                if ($index <= 1) continue; // Lewati header

                $kode = $row['A'];
                $penjualanGrouped[$kode]['user_id'] = $row['B'];
                $penjualanGrouped[$kode]['pembeli'] = $row['C'];
                $penjualanGrouped[$kode]['penjualan_tanggal'] = is_numeric($row['D'])
                    ? Date::excelToDateTimeObject($row['D'])->format('Y-m-d H:i:s')
                    : date('Y-m-d H:i:s', strtotime($row['D']));
                $penjualanGrouped[$kode]['detail'][] = [
                    'barang_id' => $row['E'],
                    'harga' => $row['F'],
                    'jumlah' => $row['G'],
                ];
            }

            // Insert data
            foreach ($penjualanGrouped as $kode => $data) {
                $penjualan = PenjualanModel::create([
                    'user_id' => $data['user_id'],
                    'pembeli' => $data['pembeli'],
                    'penjualan_kode' => $kode,
                    'penjualan_tanggal' => $data['penjualan_tanggal'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($data['detail'] as $detail) {
                    PenjualanDetailModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $detail['barang_id'],
                        'harga' => $detail['harga'],
                        'jumlah' => $detail['jumlah'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diimport.'
            ]);
        }

        return redirect('/');
    }

    public function export_excel()
    {
        $penjualan = PenjualanModel::with(['user', 'details.barang'])
            ->orderBy('penjualan_tanggal', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();

        // ================== Sheet 1: Data Penjualan ==================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Data Penjualan');

        $sheet1->setCellValue('A1', 'No');
        $sheet1->setCellValue('B1', 'Kode Transaksi');
        $sheet1->setCellValue('C1', 'Tanggal');
        $sheet1->setCellValue('D1', 'Pembeli');
        $sheet1->setCellValue('E1', 'User');
        $sheet1->setCellValue('F1', 'Total Item');
        $sheet1->setCellValue('G1', 'Total Harga');
        $sheet1->getStyle('A1:G1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;

        foreach ($penjualan as $data) {
            $total_items = $data->details->sum('jumlah');
            $total_amount = $data->getTotalAmount();

            $excelDateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($data->penjualan_tanggal));

            $sheet1->setCellValue('A' . $baris, $no);
            $sheet1->setCellValue('B' . $baris, $data->penjualan_kode);
            $sheet1->setCellValue('C' . $baris, $excelDateTime);
            $sheet1->getStyle('C' . $baris)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
            $sheet1->setCellValue('D' . $baris, $data->pembeli);
            $sheet1->setCellValue('E' . $baris, $data->user->nama);
            $sheet1->setCellValue('F' . $baris, $total_items);
            $sheet1->setCellValue('G' . $baris, $total_amount);

            $baris++;
            $no++;
        }

        foreach (range('A', 'G') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }

        // ================== Sheet 2: Detail Penjualan ==================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Detail Penjualan');

        $sheet2->setCellValue('A1', 'No');
        $sheet2->setCellValue('B1', 'Kode Transaksi');
        $sheet2->setCellValue('C1', 'Tanggal');
        $sheet2->setCellValue('D1', 'Nama Barang');
        $sheet2->setCellValue('E1', 'Jumlah');
        $sheet2->setCellValue('F1', 'Harga');
        $sheet2->setCellValue('G1', 'Subtotal');
        $sheet2->getStyle('A1:G1')->getFont()->setBold(true);

        $barisDetail = 2;
        $noDetail = 1;

        foreach ($penjualan as $data) {
            $tanggalExcel = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($data->penjualan_tanggal));
            foreach ($data->details as $detail) {
                $sheet2->setCellValue('A' . $barisDetail, $noDetail);
                $sheet2->setCellValue('B' . $barisDetail, $data->penjualan_kode);
                $sheet2->setCellValue('C' . $barisDetail, $tanggalExcel);
                $sheet2->getStyle('C' . $barisDetail)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
                $sheet2->setCellValue('D' . $barisDetail, $detail->barang->barang_nama);
                $sheet2->setCellValue('E' . $barisDetail, $detail->jumlah);
                $sheet2->setCellValue('F' . $barisDetail, $detail->harga);
                $sheet2->setCellValue('G' . $barisDetail, $detail->jumlah * $detail->harga);

                $barisDetail++;
                $noDetail++;
            }
        }

        foreach (range('A', 'G') as $columnID) {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }

        // ================== Output ==================
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Penjualan ' . date('Y-m-d H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $penjualan = PenjualanModel::with(['user', 'details.barang'])
            ->orderBy('penjualan_tanggal', 'desc')
            ->get();

        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Penjualan ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
