<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Retur - {{ $record->reference_number }}</title>
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
        .text-right { text-align: right; }

        .signatures { display: table; width: 100%; margin-top: 40px; text-align: center; }
        .sig-box { display: table-cell; width: 33.33%; vertical-align: top; }
        .sig-space { height: 60px; }

        @media print {
            body { padding: 1; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol Navigasi Layar (Hilang saat diprint) -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer; font-weight: bold;">Cetak Dokumen</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <div class="header">
        <div class="company-info">
            <h2>DC JB PRINTING</h2>
            <p>Pusat Layanan Percetakan & Konveksi</p>
        </div>
        <div class="doc-title">
            <h1>SURAT JALAN RETUR BARANG</h1>
            <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 110px;"><strong>Jenis Retur:</strong></td>
                    <td>
                        @if($record->return_type === 'to_supplier') Retur ke Supplier
                        @elseif($record->return_type === 'branch_to_hq') Retur Cabang ke Pusat
                        @else Retur Konsumen ke Pusat
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Cabang/Lokasi:</strong></td>
                    <td>{{ $record->branch->name ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 110px;"><strong>No. Referensi:</strong></td>
                    <td>{{ $record->reference_number }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Retur:</strong></td>
                    <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>{{ $record->status }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Barang / Produk</th>
                <th style="width: 80px; text-align: center;">Qty</th>
                <th style="width: 130px; text-align: right;">Harga Satuan</th>
                <th style="width: 130px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="center">{{ $item->quantity ?? 0 }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(($item->quantity * ($item->unit_price ?? 0)), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total Keseluruhan:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($record->total_amount ?? 0, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sig-box">
            <p>Penerima / Cabang,</p>
            <div class="sig-space"></div>
            <p><strong>( ........................ )</strong></p>
        </div>
        <div class="sig-box">
            <p>Pengirim / Kurir,</p>
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