<body onload="window.print()" style="font-family: Arial, sans-serif; padding: 20px;">

<!-- Header Laporan -->
<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
    <h2 style="margin: 0;">DC JB PRINTING</h2>
    
    @if(request('type') == 'piutang')
        <h3 style="margin: 5px 0;">Laporan Piutang</h3>
    @elseif(request('type') == 'hutang')
        <h3 style="margin: 5px 0;">Laporan Hutang</h3>
    @elseif(request('type') == 'stock-in' || request('type') == 'barang-masuk')
        <h3 style="margin: 5px 0;">Laporan Data Barang Masuk</h3>
    @else
        <h3 style="margin: 5px 0;">Laporan Data Barang Keluar</h3>
    @endif
    
    <p style="margin: 0; font-size: 12px; color: #666;">Dicetak pada: {{ date('d M Y H:i') }}</p>
</div>

<!-- Tabel Data -->
<table border="1" style="width: 100%; border-collapse: collapse; font-size: 11px;">
    <thead>
        <tr style="background-color: #333; color: white;">
            @if(request('type') == 'piutang')
                <th style="padding: 8px;">No. Nota</th>
                <th style="padding: 8px;">Tanggal</th>
                <th style="padding: 8px;">Customer</th>
                <th style="padding: 8px;">Nama Barang</th>
                <th style="padding: 8px;">Total Tagihan</th>
                <th style="padding: 8px;">Terbayar</th>
                <th style="padding: 8px;">Sisa Hutang</th>
                <th style="padding: 8px;">Status</th>
                <th style="padding: 8px;">Sales</th>
            @elseif(request('type') == 'hutang')
                <th style="padding: 8px;">No. Nota</th>
                <th style="padding: 8px;">Tanggal</th>
                <th style="padding: 8px;">Supplier</th>
                <th style="padding: 8px;">Nama Barang</th>
                <th style="padding: 8px;">Total Tagihan</th>
                <th style="padding: 8px;">Terbayar</th>
                <th style="padding: 8px;">Sisa Hutang</th>
                <th style="padding: 8px;">Status</th>
            @elseif(request('type') == 'stock-in' || request('type') == 'barang-masuk')
                <th style="padding: 8px;">No. Referensi</th>
                <th style="padding: 8px;">Tanggal</th>
                <th style="padding: 8px;">Barang</th>
                <th style="padding: 8px;">Cabang</th>
                <th style="padding: 8px;">Supplier</th>
                <th style="padding: 8px;">Qty</th>
                <th style="padding: 8px;">Harga Beli</th>
                <th style="padding: 8px;">Total Harga</th>
            @else
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
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($records as $item)
        <tr>
            @if(request('type') == 'piutang')
                <td style="padding: 6px;">{{ $item->no_nota ?? $item->no_ref ?? $item->reference_number ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->tanggal ?? $item->created_at?->format('d/m/y') ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->customer->name ?? $item->customer_name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->nama_barang ?? $item->barang?->name ?? '-' }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->total_tagihan ?? $item->total ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->terbayar ?? $item->paid ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->sisa_hutang ?? $item->remaining ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">{{ $item->status ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->sales->name ?? $item->sales_name ?? '-' }}</td>
            @elseif(request('type') == 'hutang')
                <td style="padding: 6px;">{{ $item->no_nota ?? $item->no_ref ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->tanggal ?? $item->created_at?->format('d/m/y') ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->supplier->name ?? $item->supplier_name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->nama_barang ?? $item->barang?->name ?? '-' }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->total_tagihan ?? $item->total ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->terbayar ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->sisa_hutang ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">{{ $item->status ?? '-' }}</td>
            @elseif(request('type') == 'stock-in' || request('type') == 'barang-masuk')
                <td style="padding: 6px;">{{ $item->no_ref ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->tanggal ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->barang->name ?? $item->nama_barang ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->cabang ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->supplier->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->qty ?? 0 }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->harga_beli ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->total_harga ?? (($item->qty ?? 0) * ($item->harga_beli ?? 0)), 0, ',', '.') }}</td>
            @else
                <td style="padding: 6px;">{{ $item->no_ref ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->tanggal ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->barang->name ?? $item->nama_barang ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->cabang ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->customer->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->supplier->name ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->tujuan ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->keperluan ?? '-' }}</td>
                <td style="padding: 6px;">{{ $item->qty ?? 0 }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->harga_beli ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
                <td style="padding: 6px;">{{ $item->sales->name ?? '-' }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

</body>