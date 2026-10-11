<?php

namespace App\Http\Controllers;

use App\Models\StockMutation; // Sesuaikan dengan nama model Anda
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function print($reference_number)
    {
        // Pastikan kita sudah melakukan Eager Loading untuk data customer dan product
        $records = StockMutation::where('reference_number', $reference_number)
            ->with(['product', 'customer']) // Pastikan nama relasi di model benar
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'Data transaksi tidak ditemukan.');
        }

        $header = $records->first();

        return view('invoices.print', compact('records', 'header', 'reference_number'));
    }

    public function printTable(Request $request)
    {
        $type = $request->query('type');
        $ids = $request->query('ids', []);

        // Ambil data murni berdasarkan array ID yang dikirim dari tombol cetak tanpa filter tambahan
        $query = StockMutation::whereIn('id', $ids);

        if ($type == 'piutang') {
            $title = 'Laporan Piutang';
            $columns = ['No. Nota', 'Tanggal', 'Customer', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Piutang', 'Status', 'Sales'];

            $rowCallback = function ($item) {
                $totalTagihan = ($item->price ?? 0) * ($item->quantity ?? 0);
                if ($totalTagihan == 0) {
                    $totalTagihan = $item->subtotal ?? $item->total ?? 0;
                }

                // Jika status lunas, samakan dengan Filament (Terbayar tampil 0 atau sesuai paid_amount jika tidak dihitung cicilan)
                $terbayar = 0;
                if ($item->payment_status !== 'lunas') {
                    if (method_exists($item, 'debtPayments')) {
                        $terbayar = $item->debtPayments()->sum('amount_paid') ?? 0;
                    }
                    if ($terbayar == 0) {
                        $terbayar = $item->paid_amount ?? 0;
                    }
                }

                $sisa = ($item->payment_status === 'lunas') ? 0 : ($totalTagihan - $terbayar);

                $customerName = $item->customer->name ?? $item->nama_customer ?? '-';
                $salesName = $item->salesPerson->name ?? $item->sales->name ?? $item->sales_person ?? '-';

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $customerName,
                    $item->product->name ?? $item->nama_barang ?? '-',
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    'Rp ' . number_format($terbayar, 0, ',', '.'),
                    'Rp ' . number_format($sisa, 0, ',', '.'),
                    $item->payment_status ?? 'hutang',
                    $salesName
                ];
            };

            // Ambil data dan hitung baris summary agar sinkron 100% dengan Filament
            $records = $query->get();
            $sumTagihan = $records->sum(fn($i) => (($i->price ?? 0) * ($i->quantity ?? 0)) ?: ($i->subtotal ?? $i->total ?? 0));

            $sumTerbayar = $records->sum(function ($i) {
                if (($i->payment_status ?? 'hutang') === 'lunas') {
                    return 0; // Sesuai dengan panel Filament di mana transaksi lunas nilai terbayarnya dihitung 0 / tidak masuk akumulasi cicilan
                }
                $paid = 0;
                if (method_exists($i, 'debtPayments')) {
                    $paid = $i->debtPayments()->sum('amount_paid') ?? 0;
                }
                if ($paid == 0) {
                    $paid = $i->paid_amount ?? 0;
                }
                return $paid;
            });

            $sumSisa = $records->sum(function ($i) {
                if (($i->payment_status ?? 'hutang') === 'lunas') return 0;
                $tTagihan = (($i->price ?? 0) * ($i->quantity ?? 0)) ?: ($i->subtotal ?? $i->total ?? 0);
                $tBayar = method_exists($i, 'debtPayments') ? ($i->debtPayments()->sum('amount_paid') ?? 0) : ($i->paid_amount ?? 0);
                return max(0, $tTagihan - $tBayar);
            });

            $totals = [
                'colspan' => 4,
                'values' => [
                    '',
                    'Rp ' . number_format($sumTagihan, 0, ',', '.'),
                    'Rp ' . number_format($sumTerbayar, 0, ',', '.'),
                    'Rp ' . number_format($sumSisa, 0, ',', '.'),
                    '',
                    ''
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));
        } elseif ($type == 'hutang') {
            $title = 'Laporan Hutang';
            $columns = ['No. Nota', 'Tanggal', 'Supplier', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Hutang', 'Status'];

            $rowCallback = function ($item) {
                $totalTagihan = $item->subtotal ?? $item->total ?? 0;

                // Menjumlahkan amount_paid dari relasi debtPayments
                $terbayar = $item->debtPayments()->sum('amount_paid') ?? 0;

                // Fallback jika paid_amount di stock_mutations juga terisi
                if ($terbayar == 0) {
                    $terbayar = $item->paid_amount ?? 0;
                }

                $sisa = $totalTagihan - $terbayar;

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->supplier->name ?? '-',
                    $item->product->name ?? '-',
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    'Rp ' . number_format($terbayar, 0, ',', '.'),
                    'Rp ' . number_format($sisa, 0, ',', '.'),
                    $item->payment_status ?? '-'
                ];
            };

            // Ambil data untuk baris summary
            $records = $query->get();
            $sumTagihan = $records->sum(fn($i) => $i->subtotal ?? $i->total ?? 0);

            $sumTerbayar = $records->sum(function ($i) {
                $paid = $i->debtPayments()->sum('amount_paid') ?? 0;
                if ($paid == 0) {
                    $paid = $i->paid_amount ?? 0;
                }
                return $paid;
            });

            $sumSisa = $sumTagihan - $sumTerbayar;

            $totals = [
                'colspan' => 5,
                'values' => [
                    'Rp ' . number_format($sumTagihan, 0, ',', '.'),
                    'Rp ' . number_format($sumTerbayar, 0, ',', '.'),
                    'Rp ' . number_format($sumSisa, 0, ',', '.'),
                    ''
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));
        } elseif ($type == 'stock-in' || $type == 'masuk') {
            $title = 'Laporan Data Barang Masuk';
            $columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Supplier', 'Qty', 'Harga Beli', 'Total Harga'];

            $rowCallback = function ($item) {
                $branchName = '-';
                if (is_object($item->branch)) {
                    $branchName = $item->branch->name ?? '-';
                } elseif (is_array($item->branch)) {
                    $branchName = $item->branch['name'] ?? '-';
                } else {
                    $branchName = $item->branch ?? '-';
                }

                $qty = $item->quantity ?? 0;
                $hargaBeli = $item->purchase_price ?? 0;
                $totalHarga = $item->subtotal ?? ($qty * $hargaBeli);

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,
                    $item->supplier->name ?? '-',
                    $qty,
                    'Rp ' . number_format($hargaBeli, 0, ',', '.'),
                    'Rp ' . number_format($totalHarga, 0, ',', '.')
                ];
            };

            // Ambil data dan hitung baris summary untuk laporan barang masuk
            $records = $query->get();
            $sumQty = $records->sum(fn($i) => $i->quantity ?? 0);
            $sumTotalHarga = $records->sum(fn($i) => $i->subtotal ?? (($i->quantity ?? 0) * ($i->purchase_price ?? 0)));

            $totals = [
                'colspan' => 6, // Mengatur jumlah kolom yang dilewati sebelum kolom Qty
                'values' => [
                    $sumQty,                                    // Masuk ke kolom Qty
                    '',                                         // Masuk ke kolom Harga Beli (dikosongkan)
                    'Rp ' . number_format($sumTotalHarga, 0, ',', '.') // Masuk ke kolom Total Harga
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));
        } else {
            $title = 'Laporan Data Barang Keluar';
            $columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Customer', 'Qty', 'Harga Jual', 'Total'];
            $rowCallback = function ($item) {
                $branchName = '-';
                if (is_object($item->branch)) {
                    $branchName = $item->branch->name ?? '-';
                } elseif (is_array($item->branch)) {
                    $branchName = $item->branch['name'] ?? '-';
                } else {
                    $branchName = $item->branch ?? '-';
                }

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,
                    $item->customer->name ?? '-',
                    $item->quantity ?? 0,
                    'Rp ' . number_format($item->price ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.')
                ];
            };

            $records = $query->get();
            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback'));
        }
    }
}
