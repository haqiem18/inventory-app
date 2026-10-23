<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $firstRecord->reference_number ?? ($reference ?? 'SJ') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 15px; }
        .container { max-width: 800px; margin: auto; background: #fff; }
        
        /* Header Grid */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .logo-area { width: 35%; }
        .logo { max-width: 180px; height: auto; }
        
        .header-right { width: 45%; text-align: left; }
        .surat-jalan-title { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .customer-name { font-size: 18px; font-weight: bold; margin-top: 2px; }
        .sj-number { font-size: 14px; font-weight: bold; margin-top: 2px; }
        
        .company-address { font-size: 10px; line-height: 1.3; margin-top: 5px; }
        .header-line { border-bottom: 2px solid #000; margin: 12px 0; }

        /* Main Table */
        table.sj-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.sj-table th, table.sj-table td { border: 1px solid #000; padding: 5px 8px; }
        table.sj-table th { background-color: #fff; font-weight: bold; text-align: center; }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Summary Row */
        .summary-row td { border: 1px solid #000; font-weight: bold; background-color: #fff; }

        /* Note Section */
        .note-text { font-size: 11px; margin-top: 10px; font-weight: bold; }

        /* Signatures */
        .footer-grid { margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-start; font-size: 11px; }
        .signature-area { width: 30%; text-align: center; }
        .signature-space { height: 45px; }
        .sales-info { margin-top: 10px; font-size: 11px; font-weight: bold; }

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
            
            $namaTujuan = 'JB CIATEUL';
            $salesName = null;

            if ($firstRecord) {
                if ($firstRecord->sub_type === 'mutasi' && $firstRecord->toBranch) {
                    $namaTujuan = $firstRecord->toBranch->name;
                } elseif ($firstRecord->customer) {
                    $namaTujuan = $firstRecord->customer->name;
                } elseif ($firstRecord->supplier) {
                    $namaTujuan = $firstRecord->supplier->name;
                } elseif (!empty($firstRecord->nama_customer)) {
                    $namaTujuan = $firstRecord->nama_customer;
                }

                // Ambil data sales jika ada relasinya
                $salesName = $firstRecord->salesPerson->name ?? $firstRecord->sales->name ?? $firstRecord->sales_person ?? null;
            }
        @endphp

        <!-- HEADER KOP -->
        <div class="header-top">
            <div class="logo-area">
                <img src="{{ asset('images/logo.png') }}" alt="JB PRINTING" class="logo" onerror="this.style.display='none'">
                <div class="company-address">
                    Jl. Cibaligo No 21/8 Cibeureum<br>
                    Cimahi Selatan - Cimahi
                </div>
            </div>
            <div class="header-right">
                <div class="surat-jalan-title">SURAT JALAN</div>
                <div class="customer-name">{{ strtoupper($namaTujuan) }}</div>
                <div class="sj-number">{{ $firstRecord->reference_number ?? ($reference ?? 'JBI.26.1059') }}</div>
            </div>
        </div>

        <div class="header-line"></div>

        <!-- TABEL SURAT JALAN -->
        <table class="sj-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No.</th>
                    <th style="width: 44%;" class="text-left">NAMA STYLE</th>
                    <th style="width: 20%;">WARNA</th>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 20%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalQty = 0;
                    $maxRows = 8; 
                @endphp

                @foreach($records as $index => $item)
                    @php 
                        $qty = $item->quantity ?? $item->qty ?? 0;
                        $totalQty += $qty;

                        // Mengambil satuan dari relasi produk (unit / satuan)
                        $satuanBarang = $item->product->unit ?? $item->product->satuan ?? $item->satuan ?? '';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->product->name ?? $item->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $item->color ?? $item->warna ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-center">{{ $satuanBarang }}</td>
                    </tr>
                @endforeach

                <!-- Baris Kosong Tambahan -->
                @for($i = count($records); $i < $maxRows; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="3" class="text-right" style="padding-right: 15px;"><strong>Total</strong></td>
                    <td class="text-center"><strong>{{ $totalQty }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- NOTE -->
        <div class="note-text">
            Note : Claim kekurangan barang tidak boleh lebih 2 hari
        </div>

        <!-- TANDA TANGAN & SALES -->
        <div class="footer-grid">
            <div class="signature-area" style="text-align: left;">
                Penerima
                <div class="signature-space"></div>
                ( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )
            </div>

            <div class="signature-area" style="text-align: right;">
                Cimahi, {{ date('d F Y') }}<br>
                Hormat kami
                <div class="signature-space"></div>
                ( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )
                @if($salesName)
                    <div class="sales-info">Sales: {{ $salesName }}</div>
                @endif
            </div>
        </div>

        <button class="no-print" onclick="window.print()">Cetak Surat Jalan</button>
    </div>
</body>
</html>