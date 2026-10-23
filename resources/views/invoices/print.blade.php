<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $reference_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 15px; }
        .container { max-width: 800px; margin: auto; background: #fff; }
        
        /* Header Grid */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .logo-area { width: 35%; }
        .logo { max-width: 180px; height: auto; }
        .header-center { width: 40%; text-align: center; }
        .invoice-title { font-size: 22px; font-weight: bold; letter-spacing: 2px; margin-bottom: 5px; }
        .company-address { font-size: 10px; line-height: 1.3; }
        
        .header-line { border-bottom: 2px solid #000; margin-bottom: 12px; }

        /* Meta Info (TO & Date / No SJ / Sales) */
        .meta-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .meta-table td { padding: 2px 0; vertical-align: top; }

        /* Main Invoice Table */
        table.invoice-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.invoice-table th, table.invoice-table td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; }
        table.invoice-table th { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; background-color: #fff; }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Summary / Total Section */
        .summary-row td { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; }
        
        .total-box-table { width: 50%; float: right; border-collapse: collapse; font-size: 11px; margin-top: -1px; }
        .total-box-table td { border: 1px solid #000; padding: 4px 8px; font-weight: bold; }

        /* Clearfix */
        .clearfix::after { content: ""; clear: both; display: table; }

        /* Signatures & Bank Details */
        .footer-grid { margin-top: 25px; display: flex; justify-content: space-between; align-items: flex-start; }
        .bank-details { font-size: 10px; line-height: 1.4; width: 40%; }
        .signature-box { text-align: center; width: 25%; font-size: 11px; }
        .signature-line { margin-top: 50px; border-bottom: 1px dotted #000; width: 80%; margin-left: auto; margin-right: auto; }

        /* Notes Box */
        .notes-box { border: 1px solid #000; padding: 5px 8px; font-size: 9px; line-height: 1.3; margin-top: 20px; font-weight: bold; width: 70%; }

        .no-print { margin-top: 20px; padding: 8px 15px; cursor: pointer; display: inline-block; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        
        @php
            $firstRecord = isset($records) && $records->isNotEmpty() ? $records->first() : null;
            $salesName = null;
            $customerAddress = '-';

            if ($firstRecord) {
                $salesName = $firstRecord->salesPerson->name ?? $firstRecord->sales->name ?? $firstRecord->sales_person ?? null;
                
                if (isset($header->customer->address)) {
                    $customerAddress = $header->customer->address;
                } elseif (isset($firstRecord->customer->address)) {
                    $customerAddress = $firstRecord->customer->address;
                } elseif (isset($firstRecord->alamat_customer)) {
                    $customerAddress = $firstRecord->alamat_customer;
                }
            }
        @endphp

        <!-- HEADER KOP -->
        <div class="header-top">
            <div class="logo-area">
                <img src="{{ asset('images/logo.png') }}" alt="JB PRINTING" class="logo" onerror="this.style.display='none'">
            </div>
            <div class="header-center">
                <div class="invoice-title">INVOICE</div>
                <div class="company-address">
                    JL. Cibaligo No. 21/8 Cibeureum<br>
                    Cimahi Selatan - Jawa Barat
                </div>
            </div>
            <div style="width: 25%;"></div>
        </div>

        <div class="header-line"></div>

        <!-- INFORMASI PELANGGAN & DETAIL NOTA -->
        <table class="meta-table">
            <tr>
                <td style="width: 8%;"><strong>TO:</strong></td>
                <td style="width: 42%;"><strong>{{ strtoupper($header->customer->name ?? 'JB CIATEUL') }}</strong></td>
                <td style="width: 12%;">Date :</td>
                <td style="width: 38%; border-bottom: 1px solid #000;">{{ $header->created_at ? $header->created_at->format('d F Y') : date('d F Y') }}</td>
            </tr>
            <tr>
                <td><strong>Alamat:</strong></td>
                <td>{{ $customerAddress }}</td>
                <td>No SJ :</td>
                <td style="border-bottom: 1px solid #000;">{{ $header->reference_number ?? $reference_number }}</td>
            </tr>
            @if($salesName)
            <tr>
                <td></td>
                <td></td>
                <td>Sales :</td>
                <td><strong>{{ $salesName }}</strong></td>
            </tr>
            @endif
        </table>

        <!-- TABEL BARANG -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No.</th>
                    <th style="width: 50%;" class="text-left">Descriptions</th>
                    <th style="width: 12%;">Qty</th>
                    <th style="width: 16%;">Price<br><span style="font-weight: normal;">Rp</span></th>
                    <th style="width: 16%;">Amount<br><span style="font-weight: normal;">Rp</span></th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandTotal = 0; 
                    $totalQty = 0;
                    $maxRows = 8; 
                @endphp

                @foreach($records as $index => $item)
                    @php 
                        $qty = $item->quantity ?? 0;
                        $price = $item->price ?? 0;
                        $totalItem = $item->subtotal ?? ($qty * $price);
                        
                        $totalQty += $qty;
                        $grandTotal += $totalItem;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->product->name ?? $item->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-right">{{ $price > 0 ? number_format($price, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $totalItem > 0 ? number_format($totalItem, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach

                <!-- Baris Kosong Tambahan jika data < 8 -->
                @for($i = count($records); $i < $maxRows; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right">-</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- BARIS SUMMARY / TOTAL QTY & AMOUNT -->
        <table class="invoice-table">
            <tr class="summary-row">
                <td style="width: 56%; border-top: 1px solid #000; border-bottom: 1px solid #000;"></td>
                <td style="width: 12%; border-top: 1px solid #000; border-bottom: 1px solid #000;" class="text-center">
                    <strong>{{ $totalQty }}</strong>
                </td>
                <td style="width: 16%; border-top: 1px solid #000; border-bottom: 1px solid #000;"></td>
                <td style="width: 16%; border-top: 1px solid #000; border-bottom: 1px solid #000;" class="text-right">
                    <strong>{{ number_format($grandTotal, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </table>

        <!-- TOTAL & DEPOSIT BOX -->
        <div class="clearfix">
            <table class="total-box-table">
                <tr>
                    <td style="width: 50%;" class="text-center">DEPOSIT</td>
                    <td style="width: 50%;" class="text-right">-</td>
                </tr>
                <tr>
                    <td class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- TANDA TERIMA & HORMAT KAMI -->
        <div class="footer-grid">
            <div class="bank-details">
                <strong>Bank Details :</strong><br>
                BERTON<br>
                BANK CENTRAL ASIA<br>
                ACCOUNT NO : 838 265 1668
            </div>

            <div class="signature-box">
                Tanda Terima
                <div class="signature-line"></div>
            </div>

            <div class="signature-box">
                Hormat Kami.
                <div class="signature-line"></div>
            </div>
        </div>

        <!-- CATATAN FOOTER -->
        <div class="notes-box">
            PEMBAYARAN SAH HANYA MELALUI REKENING TERTERA<br>
            PEMBAYARAN DILUAR REKENING TERTERA DIANGGAP BELUM MEMBAYAR TAGIHAN<br>
            BUKTI TRANSFER DAPAT DIKIRIM KE WA NO 0877-7888-1668
        </div>

        <button class="no-print" onclick="window.print()">Cetak Invoice</button>
    </div>
</body>
</html>