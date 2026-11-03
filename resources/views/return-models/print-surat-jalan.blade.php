<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Retur - {{ $record->reference_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .signatures { margin-top: 50px; width: 100%; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Dokumen</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <div class="header">
        <h2>SURAT JALAN RETUR BARANG</h2>
        <p>No. Referensi: <strong>{{ $record->reference_number }}</strong></p>
    </div>

    <div class="details">
        <p><strong>Jenis Retur:</strong> 
            @if($record->return_type === 'to_supplier') Retur ke Supplier
            @elseif($record->return_type === 'branch_to_hq') Retur Cabang ke Pusat
            @else Retur Konsumen ke Pusat
            @endif
        </p>
        <p><strong>Tanggal:</strong> {{ $record->created_at->format('d M Y H:i') }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'Produk #' . $item->product_id }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(($item->quantity * ($item->price ?? 0)), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures" style="border: none;">
        <tr style="border: none;">
            <td style="border: none; text-align: center; width: 50%;">
                <p>Hormat Kami,</p>
                <br><br><br>
                <p>( .................................... )</p>
            </td>
            <td style="border: none; text-align: center; width: 50%;">
                <p>Disetujui Oleh,</p>
                <br><br><br>
                <p>( .................................... )</p>
            </td>
        </tr>
    </table>

</body>
</html>