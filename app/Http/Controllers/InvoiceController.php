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
            $columns = ['No. Nota', 'Tanggal', 'Customer', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Piutang', 'Status'];

            $rowCallback = function ($item) {
                $totalTagihan = $item->subtotal ?? $item->total ?? 0;

                // Menjumlahkan pembayaran dari relasi pembayaran piutang jika ada (misal: receivablePayments atau debtPayments/payments)
                // Sesuaikan nama relasi jika menggunakan nama lain, atau fallback ke paid_amount
                $terbayar = 0;
                if (method_exists($item, 'receivablePayments')) {
                    $terbayar = $item->receivablePayments()->sum('amount_paid') ?? 0;
                } elseif (method_exists($item, 'payments')) {
                    $terbayar = $item->payments()->sum('amount_paid') ?? 0;
                }

                if ($terbayar == 0) {
                    $terbayar = $item->paid_amount ?? 0;
                }

                $sisa = $totalTagihan - $terbayar;

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->customer->name ?? '-',
                    $item->product->name ?? '-',
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    'Rp ' . number_format($terbayar, 0, ',', '.'),
                    'Rp ' . number_format($sisa, 0, ',', '.'),
                    $item->payment_status ?? '-'
                ];
            };

            // Ambil data dan hitung baris summary untuk laporan piutang
            $records = $query->get();
            $sumTagihan = $records->sum(fn($i) => $i->subtotal ?? $i->total ?? 0);

            $sumTerbayar = $records->sum(function ($i) {
                $paid = 0;
                if (method_exists($i, 'receivablePayments')) {
                    $paid = $i->receivablePayments()->sum('amount_paid') ?? 0;
                } elseif (method_exists($i, 'payments')) {
                    $paid = $i->payments()->sum('amount_paid') ?? 0;
                }
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

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,
                    $item->supplier->name ?? '-',
                    $item->quantity ?? 0,
                    'Rp ' . number_format($item->purchase_price ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->subtotal ?? (($item->quantity ?? 0) * ($item->purchase_price ?? 0)), 0, ',', '.')
                ];
            };

            $records = $query->get();
            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback'));
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
