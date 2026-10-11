<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - DC JB PRINTING</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .company-info h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .company-info p { margin: 2px 0; font-size: 11px; color: #555; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        .doc-title p { margin: 2px 0; }
        
        .info-section { display: table; width: 100%; margin-bottom: 15px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-box table { width: 100%; font-size: 12px; }
        .info-box td { padding: 2px 0; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items-table th, table.items-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        table.items-table th { background-color: #f2f2f2; text-align: center; }
        table.items-table td.center { text-align: center; }

        .signatures { display: table; width: 100%; margin-top: 40px; text-align: center; }
        .sig-box { display: table-cell; width: 33.33%; vertical-align: top; }
        .sig-space { height: 60px; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="company-info">
            <h2>DC JB PRINTING</h2>
            <p>Pusat Layanan Percetakan & Konveksi</p>
        </div>
        <div class="doc-title">
            <h1>SURAT JALAN</h1>
            <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
        </div>
    </div>

    @php
        $first = $records->first();

        // Logika penentuan nama dan alamat tujuan yang dinamis
        $namaTujuan = '-';
        $alamatTujuan = '-';

        if ($first) {
            if ($first->sub_type === 'mutasi' && $first->toBranch) {
                $namaTujuan = 'Cabang Tujuan: ' . $first->toBranch->name;
                $alamatTujuan = $first->toBranch->address ?? '-';
            } elseif ($first->customer) {
                $namaTujuan = $first->customer->name;
                $alamatTujuan = $first->customer->address ?? '-';
            } elseif ($first->supplier) {
                $namaTujuan = $first->supplier->name;
                $alamatTujuan = $first->supplier->address ?? '-';
            } else {
                $namaTujuan = $first->nama_customer ?? '-';
            }
        }
    @endphp

    <div class="info-section">
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 100px;"><strong>Kepada Yth:</strong></td>
                    <td>{{ $namaTujuan }}</td>
                </tr>
                <tr>
                    <td><strong>Alamat:</strong></td>
                    <td>{{ $alamatTujuan }}</td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 100px;"><strong>No. Nota/Ref:</strong></td>
                    <td>{{ $first->reference_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Kirim:</strong></td>
                    <td>{{ $first->mutation_date ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Cabang Asal:</strong></td>
                    <td>{{ $first->branch->name ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Barang</th>
                <th style="width: 80px; text-align: center;">Qty</th>
                <th style="width: 150px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? $item->nama_barang ?? '-' }}</td>
                <td class="center">{{ $item->quantity ?? 0 }}</td>
                <td>{{ $item->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-box">
            <p>Penerima,</p>
            <div class="sig-space"></div>
            <p><strong>( ........................ )</strong></p>
        </div>
        <div class="sig-box">
            <p>Hormat Kami,</p>
            <div class="sig-space"></div>
            <p><strong>( ........................ )</strong></p>
        </div>
        <div class="sig-box">
            <p>Disetujui Oleh,</p>
            <div class="sig-space"></div>
            <p><strong>( ........................ )</strong></p>
        </div>
    </div>

</body>
</html>