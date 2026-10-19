<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { float: right; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; }
        .signature { margin-top: 50px; text-align: right; }
        .no-print { margin-top: 20px; padding: 10px 20px; cursor: pointer; background: #2c3e50; color: white; border: none; border-radius: 4px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header-grid">
            <div>
                <!-- Pastikan file logo ada di public/images/logo.png atau sesuaikan -->
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo" onerror="this.style.display='none'">
                <h2 style="color: #2c3e50; margin: 10px 0 0 0;">DC JB PRINTING</h2>
            </div>
            <div style="text-align: right;">
                <h2 style="color: #3498db; margin: 0;">PURCHASE ORDER</h2>
                <p>No. PO: <strong>{{ $purchaseOrder->po_number }}</strong><br>
                    Tanggal: {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="box">
                <strong>Cabang Pemesan</strong><br>
                {{ $purchaseOrder->branch->name ?? 'Pusat' }}<br>
                Bandung Timur<br>
            </div>
            <div class="box">
                <strong>Supplier</strong><br>
                {{ $purchaseOrder->supplier->name ?? '-' }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga Beli</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->sku ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Total Biaya:</span> 
                <span>Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="signature">
            <p>Dengan Hormat,</p>
            <br><br><br>
            <p><strong>Management DC JB Printing</strong></p>
        </div>

        <button class="no-print" onclick="window.print()">Cetak Dokumen PO</button>
    </div>
</body>
</html>