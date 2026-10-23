<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $reference_number }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #000; margin: 0; padding: 10px; }
        .container { max-width: 900px; margin: auto; background: #fff; }
        
        /* Header Grid */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo-area { display: flex; align-items: center; gap: 15px; }
        .logo { max-height: 50px; }
        .invoice-title { font-size: 26px; font-weight: bold; letter-spacing: 3px; text-align: right; }
        
        /* Meta Info */
        .meta-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .meta-table td { padding: 2px 0; border: none; }
        
        /* Main Table with Multi-level Header */
        table.invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        table.invoice-table th, table.invoice-table td { border: 1px solid #000; padding: 6px 8px; text-align: center; }
        table.invoice-table th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }

        /* Footer / Notes / Bank Details */
        .footer-section { display: flex; justify-content: space-between; margin-top: 20px; align-items: flex-start; }
        .bank-details { font-size: 11px; line-height: 1.4; }
        
        .notes-box { border: 1px solid #000; padding: 8px; font-size: 10px; margin-top: 15px; width: 60%; line-height: 1.3; }
        
        .no-print { margin-top: 20px; padding: 8px 15px; cursor: pointer; display: inline-block; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        
        <!-- HEADER KOP & JUDUL -->
        <div class="header-top">
            <div class="logo-area">
                <!-- Sesuaikan path logo Anda -->
                <img src="{{ asset('images/logo.png') }}" alt="Logo JB" class="logo" onerror="this.style.display='none'">
                <div>
                    <h3 style="margin: 0; font-size: 16px;">DC JB PRINTING</h3>
                </div>
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
            </div>
        </div>

        <!-- INFORMASI TUJUAN & TANGGAL -->
        <table class="meta-table">
            <tr>
                <td style="width: 8%;"><strong>TO:</strong></td>
                <td style="width: 52%;"><strong>{{ strtoupper($header->customer->name ?? 'PELANGGAN UMUM') }}</strong></td>
                <td style="width: 12%;">Date</td>
                <td style="width: 28%;">: {{ $header->created_at->format('d F Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>Inv. No</td>
                <td>: {{ $reference_number }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>No SJ</td>
                <td>: {{ $header->reference_number ?? '-' }}</td>
            </tr>
        </table>

        <!-- TABEL RINCIAN ITEM (GAYA EXCEL) -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No.</th>
                    <th rowspan="2" style="width: 27%;">Descriptions</th>
                    <th colspan="4" style="width: 32%;">MATERIAL</th>
                    <th colspan="3" style="width: 24%;">QTY</th>
                    <th rowspan="2" style="width: 12%;">Total Amount</th>
                </tr>
                <tr>
                    <th>TYPE</th>
                    <th>Kg</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>MTR</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($records as $index => $item)
                    @php 
                        $qty = $item->quantity ?? 0;
                        $price = $item->price ?? 0;
                        $totalItem = $item->subtotal ?? ($qty * $price);
                        $grandTotal += $totalItem;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->product->name ?? $item->nama_barang ?? '-' }}</td>
                        <td>{{ $item->material_type ?? 'SATIN' }}</td>
                        <td>{{ $item->kg ?? '-' }}</td>
                        <td>{{ $item->material_price ? number_format($item->material_price, 0, ',', '.') : '-' }}</td>
                        <td>{{ $item->material_amount ? number_format($item->material_amount, 0, ',', '.') : '-' }}</td>
                        <td>{{ $qty }}</td>
                        <td>{{ number_format($price, 0, ',', '.') }}</td>
                        <td>{{ number_format($totalItem, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($totalItem, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <!-- Baris Kosong Tambahan agar mirip format form excel jika item sedikit -->
                @for($i = count($records); $i < 5; $i++)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="text-left"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right" style="font-weight: bold;">Total</td>
                    <td><strong>{{ $records->sum('quantity') }}</strong></td>
                    <td></td>
                    <td></td>
                    <td class="text-right"><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right" style="font-weight: bold; background: #f9f9f9;">TOTAL</td>
                    <td class="text-right" style="font-weight: bold; background: #f9f9f9;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- BANK DETAILS & CATATAN -->
        <div class="footer-section">
            <div class="bank-details">
                <strong>Bank Details :</strong><br>
                BERTON<br>
                BANK CENTRAL ASIA<br>
                ACCOUNT NO. 777-266-1668
            </div>
        </div>

        <div class="notes-box">
            <strong>CATATAN :</strong><br>
            PEMBAYARAN SAH HANYA MELALUI REKENING TERTERA<br>
            PEMBAYARAN DILUAR REKENING TERTERA DIANGGAP BELUM MEMBAYAR TAGIHAN<br>
            BUKTI TRANSFER DAPAT DIKIRIM MELALUI ADMIN JB PRINTING ATAU WA NO 0878-8822-1668<br>
            PENGEMBALIAN DANA KARENA KESALAHAN TRANSFER AKAN DIPROSES MINIMAL 3 HARI KERJA
        </div>

        <div style="text-align: right; margin-top: 30px;">
            <p>Mengetahui,</p>
            <br><br><br>
            <p><strong>Management</strong></p>
        </div>

        <button class="no-print" onclick="window.print()">Cetak Invoice</button>
    </div>
</body>
</html>