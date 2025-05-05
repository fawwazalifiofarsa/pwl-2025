<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 6px 20px 5px 20px;
            line-height: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 4px 3px;
        }

        th {
            text-align: left;
        }

        .d-block {
            display: block;
        }

        img.image {
            width: auto;
            height: 80px;
            max-width: 150px;
            max-height: 150px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .p-1 {
            padding: 5px 1px 5px 1px;
        }

        .font-10 {
            font-size: 10pt;
        }

        .font-11 {
            font-size: 11pt;
        }

        .font-12 {
            font-size: 12pt;
        }

        .font-13 {
            font-size: 13pt;
        }

        .border-bottom-header {
            border-bottom: 1px solid;
        }

        .border-all,
        .border-all th,
        .border-all td {
            border: 1px solid;
        }

        .flex-container {
            display: flex;
            justify-content: space-between;
        }

        .main-table {
            width: 100%;
        }

        .detail-table {
            width: 95%;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <table class="border-bottom-header">
        <tr>
            <td width="85%">
                <span class="text-center d-block font-11 font-bold mb-1">
                    KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI
                </span>
                <span class="text-center d-block font-13 font-bold mb-1">
                    POLITEKNIK NEGERI MALANG
                </span>
                <span class="text-center d-block font-10">
                    Jl. Soekarno-Hatta No. 9 Malang 65141
                </span>
                <span class="text-center d-block font-10">
                    Telepon (0341) 484424 Pes. 101-105, 0341-484428, Fax. (0341) 484428
                </span>
                <span class="text-center d-block font-10">
                    Laman: www.polinema.ac.id
                </span>
            </td>
        </tr>
    </table>

    <h3 class="text-center">LAPORAN DATA PENJUALAN</h3>

    @php
        $totalKeseluruhan = 0;
    @endphp

    <div class="flex-container">
        <table class="border-all main-table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Kode Penjualan</th>
                    <th>Tanggal Penjualan</th>
                    <th>Pembeli</th>
                    <th class="text-right">Total Pembayaran</th>
                    <th class="text-center">Detail Penjualan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualan as $p)
                    @php
                        $totalTransaksi = $p->getTotalAmount();
                        $totalKeseluruhan += $totalTransaksi;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $p->penjualan_kode }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->penjualan_tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $p->pembeli }}</td>
                        <td class="text-right">Rp {{ number_format($totalTransaksi , 0, ',', '.') }}</td>
                        <td class="text-center">
                            <table class="border-all detail-table">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Jumlah</th>
                                        <th class="text-right">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($p->details as $detail)
                                    <tr>
                                        <td>{{ $detail->barang->barang_kode }}</td>
                                        <td>{{ $detail->barang->barang_nama }}</td>
                                        <td class="text-right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ $detail->jumlah }}</td>
                                        <td class="text-right">{{ number_format($detail->harga * $detail->jumlah, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
