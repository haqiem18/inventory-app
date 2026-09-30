<body onload="window.print()" style="font-family: Arial, sans-serif; padding: 20px;">

    <!-- Header Laporan -->
    <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <h2 style="margin: 0;">DC JB PRINTING</h2>
        <h3 style="margin: 5px 0;">Laporan Data Barang Keluar</h3>
        <p style="margin: 0; font-size: 12px; color: #666;">Dicetak pada: {{ date('d M Y H:i') }}</p>
    </div>

    <!-- Tabel Data -->
    <table border="1" style="width: 100%; border-collapse: collapse; font-size: 11px;">
        <thead>
            <tr style="background-color: #333; color: white;">
                <th style="padding: 8px;">No. Ref</th>
                <th style="padding: 8px;">Tanggal</th>
                <th style="padding: 8px;">Barang</th>
                <th style="padding: 8px;">Cabang</th>
                <th style="padding: 8px;">Customer</th>
                <th style="padding: 8px;">Supplier</th>
                <th style="padding: 8px;">Tujuan</th>
                <th style="padding: 8px;">Keperluan</th>
                <th style="padding: 8px;">Qty</th>
                <th style="padding: 8px;">Harga Beli</th>
                <th style="padding: 8px;">Harga Jual</th>
                <th style="padding: 8px;">Total</th>
                <th style="padding: 8px;">Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $item)
            <tr>
                <td style="padding: 6px;">{{ $item->reference_number }}</td>
                <td style="padding: 6px;">{{ \Carbon\Carbon::parse($item->mutation_date)->format('d/m/y') }}</td>
                <td style="padding: 6px;">{{ $item->product->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->branch->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->customer->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->supplier->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->toBranch->name ?? '-' }}</td>                
                <td style="padding: 6px;">{{ $item->sub_type->name ?? '-' }}</td>
                <td style="padding: 6px; text-align: center;">{{ $item->quantity }}</td>
                <td style="padding: 6px; text-align: right;">{{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                <td style="padding: 6px; text-align: right;">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td style="padding: 6px; text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                <td style="padding: 6px;">{{ $item->salesPerson->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #f2f2f2; font-weight: bold;">
            <tr>
                <td colspan="6" style="padding: 8px; text-align: right;">GRAND TOTAL</td>
                <td style="padding: 8px; text-align: center;">{{ $records->sum('quantity') }}</td>
                <td></td>
                <td style="padding: 8px; text-align: right;">{{ number_format($records->sum('subtotal'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div style="margin-top: 40px; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 200px;">
            <p style="margin-bottom: 60px;">Manager,</p>
            <p style="border-bottom: 1px solid #000;">( ........................... )</p>
        </div>
    </div>

    <!-- Styling khusus untuk cetak -->
    <style type="text/css" media="print">
        @page { size: landscape; margin: 10mm; }
        body { padding: 0; }
    </style>
</body>