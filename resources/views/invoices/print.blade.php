<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $reference_number }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { max-width: 900px; margin: auto; padding: 20px; }
        .header-grid { display: flex; justify-content: space-between; margin-bottom: 30px; align-items: flex-start; }
        .logo { max-width: 150px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        .box { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #2c3e50; color: white; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 10px; }
        .totals { float: right; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; }
        .signature { margin-top: 50px; text-align: right; }
        .no-print { margin-top: 20px; padding: 10px 20px; cursor: pointer; }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header-grid">
            <div>
                <!-- Pastikan file logo ada di public/images/logo.png -->
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                <h2 style="color: #2c3e50; margin: 10px 0 0 0;">JB PRINTING</h2>
            </div>
            <div style="text-align: right;">
                <h2 style="color: #3498db; margin: 0;">Invoice</h2>
                <p>Referensi: <strong>{{ $reference_number }}</strong><br>
                   Tanggal: {{ $header->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="box">
                <strong>Informasi Perusahaan</strong><br>
                {{ $header->branch->name ?? 'Pusat' }}<br>
                Jl. Raya Bandung Timur<br>
            </div>
            <div class="box">
                <strong>Tagihan Kepada</strong><br>
                {{ $header->customer->name ?? 'Pelanggan Umum' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($records as $index => $item)
                    @php 
                        $totalItem = $item->price * $item->quantity;
                        $grandTotal += $totalItem;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->sku ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($totalItem, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row"><span>Total:</span> <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span></div>
        </div>

        <div style="clear: both;"></div>

        <div class="signature">
            <p>Dengan Hormat,</p>
            <br><br><br>
            <p><strong>Management JB Printing</strong></p>
        </div>

        <button class="no-print" onclick="window.print()">Cetak Invoice</button>
    </div>
</body>
</html>