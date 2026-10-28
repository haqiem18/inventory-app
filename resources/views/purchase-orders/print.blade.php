<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #000; margin: 0; padding: 15px; }
        .container { max-width: 900px; margin: auto; background: #fff; border: 2px solid #000; padding: 10px; }

        /* Header Grid */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .company-info { font-size: 11px; line-height: 1.3; }
        .company-title { font-size: 16px; font-weight: bold; margin-bottom: 2px; }

        .po-title-box { text-align: right; }
        .po-main-title { font-size: 24px; font-weight: bold; color: #003366; letter-spacing: 1px; }

        /* Header Tables (PO Number & Vendor) */
        table.blue-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 11px; }
        table.blue-table th { background-color: #003366; color: #fff; text-align: center; padding: 4px; font-weight: bold; text-transform: uppercase; font-size: 10px; border: 1px solid #003366; }
        table.blue-table td { border: 1px solid #ccc; padding: 4px 8px; vertical-align: top; }

        .vendor-title { background-color: #003366; color: #fff; font-weight: bold; padding: 4px 8px; font-size: 11px; text-transform: uppercase; }
        .vendor-box { border: 1px solid #003366; margin-bottom: 10px; }
        .vendor-label { background-color: #dbeeff; font-size: 9px; font-weight: bold; padding: 2px 8px; color: #333; text-transform: uppercase; }
        .vendor-value { padding: 4px 8px; font-weight: bold; font-size: 11px; }

        /* Main Items Table */
        table.item-table { width: 100%; border-collapse: collapse; margin-bottom: 0px; font-size: 11px; }
        table.item-table th { background-color: #003366; color: #fff; border: 1px solid #003366; padding: 5px; text-align: center; font-weight: bold; }
        table.item-table td { border: 1px solid #ccc; padding: 5px; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Footer Calc Grid */
        .footer-grid { display: flex; justify-content: space-between; align-items: flex-start; margin-top: -1px; }
        .requisitioner-box { width: 49%; border: 1px solid #003366; }
        .requisitioner-title { background-color: #003366; color: #fff; font-weight: bold; padding: 4px 8px; text-align: center; }
        .requisitioner-space { height: 75px; }
        .requisitioner-line { border-bottom: 2px solid #000; width: 80%; margin: 0 auto; }

        table.calc-table { width: 50%; border-collapse: collapse; font-size: 11px; }
        table.calc-table td { border: 1px solid #ccc; padding: 4px 8px; }
        table.calc-table td.title { background-color: #f2f2f2; font-weight: bold; }

        .no-print { margin-top: 20px; padding: 8px 15px; cursor: pointer; background: #003366; color: white; border: none; font-weight: bold; display: inline-block; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .container { border: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">

        <!-- HEADER KOP & JUDUL PO -->
        <div class="header-top">
            <div class="company-info">
                <div class="company-title">CV SENTRA MULTI KREASI</div>
                Jl. Cibaligo No.21/8 RT.04/RW.08,<br>
                Cimahi, Jawa Barat 40535<br>
                0877-8100-1668<br>
                finance.sentramultikreasi@gmail.com
            </div>
            <div class="po-title-box">
                <div class="po-main-title">PURCHASE ORDER</div>
                <table class="blue-table" style="width: 260px; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th>P.O. NUMBER</th>
                            <th>DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><strong>{{ $purchaseOrder->po_number }}</strong></td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($purchaseOrder->order_date ?? now())->format('d F Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INFORMASI VENDOR -->
        <div class="vendor-box">
            <div class="vendor-title">VENDOR</div>

            <div class="vendor-label">NAME</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->contact_person ?? $purchaseOrder->supplier->name ?? '-' }}</div>

            <div class="vendor-label">COMPANY NAME</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->name ?? '-' }}</div>

            <div class="vendor-label">ADDRESS</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->address ?? '-' }}</div>

            <div class="vendor-label">PHONE</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->phone ?? '-' }}</div>

            <div class="vendor-label">NPWP</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->npwp ?? '-' }}</div>

            <div class="vendor-label">NO REKENING</div>
            <div class="vendor-value">{{ $purchaseOrder->supplier->bank_account ?? '-' }}</div>
        </div>

        <!-- KETENTUAN PENGIRIMAN -->
        <table class="blue-table">
            <thead>
                <tr>
                    <th style="width: 33%;">SHIPPING TERMS</th>
                    <th style="width: 33%;">SHIPPING METHOD</th>
                    <th style="width: 34%;">DELIVERY DEADLINE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $purchaseOrder->shipping_terms ?? 'Freight on Board' }}</td>
                    <td class="text-center">{{ $purchaseOrder->shipping_method ?? 'Land' }}</td>
                    <td class="text-center">{{ $purchaseOrder->delivery_deadline ? \Carbon\Carbon::parse($purchaseOrder->delivery_deadline)->format('d F Y') : \Carbon\Carbon::parse($purchaseOrder->order_date ?? now())->format('d F Y') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TABEL RINCIAN ITEM -->
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Item#</th>
                    <th style="width: 35%;">Product Description</th>
                    <th style="width: 8%;">Roll</th>
                    <th style="width: 8%;">Quantity</th>
                    <th style="width: 13%;">Unit Price</th>
                    <th style="width: 14%;">Amount</th>
                    <th style="width: 17%;">Ket</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $subtotal = 0; 
                    $maxRows = 8;
                @endphp

                @foreach($purchaseOrder->items as $index => $item)
                    @php 
                        $qty = $item->quantity ?? ($item->quantity_kg ?? ($item->quantity_roll ?? 0));
                        $price = $item->purchase_price ?? ($item->price ?? 0);
                        $itemSubtotal = isset($item->subtotal) && $item->subtotal > 0 
                            ? (float) $item->subtotal 
                            : ((float) $qty * (float) $price);
                        
                        $subtotal += $itemSubtotal;
                        
                        // Mengambil data keterangan dari item
                        $ketItem = $item->notes ?? ($item->description ?? ($item->keterangan ?? ''));
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->product->name ?? ($item->nama_barang ?? '-') }}</td>
                        <td class="text-center">{{ $item->roll ?? $qty }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                        <td class="text-left">{{ $ketItem }}</td>
                    </tr>
                @endforeach

                <!-- Baris Kosong Tambahan -->
                @for($i = count($purchaseOrder->items); $i < $maxRows; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right">-</td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- FOOTER KALKULASI & REQUISITIONER -->
        <div class="footer-grid">
            <div class="requisitioner-box">
                <div class="requisitioner-title">Requisitioner</div>
                <div class="requisitioner-space"></div>
                <div class="requisitioner-line"></div>
                <br>
            </div>

            @php
                $discount = (float) ($purchaseOrder->discount ?? 0);
                $taxPercent = 11; 
                $taxAmount = ($subtotal - $discount) * ($taxPercent / 100);
                $otherCost = (float) ($purchaseOrder->other_cost ?? 0);
                $shippingCost = (float) ($purchaseOrder->shipping_cost ?? 0);
                
                // Hitung total kotor dan proteksi dengan max(0, ...) agar tidak minus
                $rawTotal = ($subtotal - $discount) + $taxAmount + $otherCost + $shippingCost;
                $totalAmount = max(0, $rawTotal);
            @endphp

            <table class="calc-table">
                <tr>
                    <td class="title">Subtotal (Rp)</td>
                    <td class="text-right"><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td class="title">Discount (Rp)</td>
                    <td class="text-right">{{ $discount > 0 ? 'Rp '.number_format($discount, 0, ',', '.') : '' }}</td>
                </tr>
                <tr>
                    <td class="title">Sales Tax (%) &nbsp;&nbsp;&nbsp;&nbsp; {{ $taxPercent }}</td>
                    <td class="text-right">Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="title">Other Cost / Pembulatan (Rp)</td>
                    <td class="text-right">{{ $otherCost != 0 ? 'Rp '.number_format($otherCost, 0, ',', '.') : '' }}</td>
                </tr>
                <tr>
                    <td class="title">Shipping & Handling (Rp)</td>
                    <td class="text-right">{{ $shippingCost > 0 ? 'Rp '.number_format($shippingCost, 0, ',', '.') : '' }}</td>
                </tr>
                <tr>
                    <td class="title" style="background-color: #dbeeff;"><strong>Total Amount (Rp)</strong></td>
                    <td class="text-right" style="background-color: #dbeeff;"><strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <button class="no-print" onclick="window.print()">Cetak Dokumen PO</button>
    </div>
</body>
</html>