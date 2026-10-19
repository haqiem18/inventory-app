<?php

namespace App\Http\Controllers;

use App\Models\StockMutation;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function print($reference_number)
    {
        $records = StockMutation::where('reference_number',$reference_number)
            ->with(['product', 'customer'])
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'Data transaksi tidak ditemukan.');
        }

        $header =$records->first();

        return view('invoices.print', compact('records', 'header', 'reference_number'));
    }

    public function printTable(Request $request)
    {
        $type =$request->query('type');
        $ids =$request->query('ids', []);

        $query = StockMutation::whereIn('id',$ids);

        if ($type == 'piutang') {
            $title = 'Laporan Piutang';$columns = ['No. Nota', 'Tanggal', 'Customer', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Piutang', 'Status', 'Sales'];

            $rowCallback = function ($item) {$totalTagihan = ($item->price ?? 0) * ($item->quantity ?? 0);
                if ($totalTagihan == 0) {$totalTagihan = $item->subtotal ?? $item->total ?? 0;
                }

                $terbayar = 0;
                if ($item->payment_status !== 'lunas') {
                    if (method_exists($item, 'debtPayments')) {
                        $terbayar =$item->debtPayments()->sum('amount_paid') ?? 0;
                    }
                    if ($terbayar == 0) {
                        $terbayar =$item->paid_amount ?? 0;
                    }
                }

                $sisa = ($item->payment_status === 'lunas') ? 0 : ($totalTagihan -$terbayar);

                $customerName = $item->customer->name ?? $item->nama_customer ?? '-';
                $salesName =$item->salesPerson->name ?? $item->sales->name ?? $item->sales_person ?? '-';

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $customerName,
                    $item->product->name ?? $item->nama_barang ?? '-',
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    'Rp ' . number_format($terbayar, 0, ',', '.'),
                    'Rp ' . number_format($sisa, 0, ',', '.'),$item->payment_status ?? 'hutang',
                    $salesName
                ];
            };

            $records = $query->get();$sumTagihan = $records->sum(fn($i) => (($i->price ?? 0) * ($i->quantity ?? 0)) ?: ($i->subtotal ?? $i->total ?? 0));

            $sumTerbayar = $records->sum(function ($i) {
                if (($i->payment_status ?? 'hutang') === 'lunas') {
                    return 0;
                }
                $paid = 0;
                if (method_exists($i, 'debtPayments')) {
                    $paid =$i->debtPayments()->sum('amount_paid') ?? 0;
                }
                if ($paid == 0) {
                    $paid =$i->paid_amount ?? 0;
                }
                return $paid;
            });

            $sumSisa = $records->sum(function ($i) {
                if (($i->payment_status ?? 'hutang') === 'lunas') return 0;
                $tTagihan = (($i->price ?? 0) * ($i->quantity ?? 0)) ?: ($i->subtotal ?? $i->total ?? 0);
                $tBayar = method_exists($i, 'debtPayments') ? ($i->debtPayments()->sum('amount_paid') ?? 0) : ($i->paid_amount ?? 0);
                return max(0, $tTagihan -$tBayar);
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
            $title = 'Laporan Hutang';$columns = ['No. Nota', 'Tanggal', 'Supplier', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Hutang', 'Status'];

            $rowCallback = function ($item) {$totalTagihan = $item->subtotal ?? $item->total ?? 0;
                $terbayar =$item->debtPayments()->sum('amount_paid') ?? 0;
                if ($terbayar == 0) {
                    $terbayar =$item->paid_amount ?? 0;
                }
                $sisa = $totalTagihan -$terbayar;

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->supplier->name ?? '-',
                    $item->product->name ?? '-',
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    'Rp ' . number_format($terbayar, 0, ',', '.'),
                    'Rp ' . number_format($sisa, 0, ',', '.'),$item->payment_status ?? '-'
                ];
            };

            $records = $query->get();$sumTagihan = $records->sum(fn($i) => $i->subtotal ?? $i->total ?? 0);
            $sumTerbayar = $records->sum(function ($i) {
                $paid =$i->debtPayments()->sum('amount_paid') ?? 0;
                if ($paid == 0) {
                    $paid =$i->paid_amount ?? 0;
                }
                return $paid;
            });
            $sumSisa = $sumTagihan -$sumTerbayar;

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
        
        } elseif ($type == 'stock-in' \vert{}\vert{}$type == 'masuk') {
            $title = 'Laporan Data Barang Masuk';$columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Supplier', 'Qty', 'Harga Beli', 'Total Harga'];

            $rowCallback = function ($item) {
                $branchName = is_object($item->branch) ? ($item->branch->name ?? '-') : ($item->branch ?? '-');
                $qty =$item->quantity ?? 0;
                $hargaBeli =$item->purchase_price ?? 0;
                $totalHarga =$item->subtotal ?? ($qty * $hargaBeli);

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,$item->supplier->name ?? '-',
                    $qty,
                    'Rp ' . number_format($hargaBeli, 0, ',', '.'),
                    'Rp ' . number_format($totalHarga, 0, ',', '.')
                ];
            };

            $records =$query->get();
            $sumQty =$records->sum(fn($i) =>$i->quantity ?? 0);
            $sumTotalHarga =$records->sum(fn($i) =>$i->subtotal ?? (($i->quantity ?? 0) * ($i->purchase_price ?? 0)));

            $totals = [
                'colspan' => 5,
                'values' => [
                    $sumQty,
                    '',
                    'Rp ' . number_format($sumTotalHarga, 0, ',', '.')
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));

        } elseif ($type == 'penggunaan' || $type == 'produksi' \vert{}\vert{}$type == 'operasional') {
            $title = 'Laporan Penggunaan';$columns = ['No. Ref', 'Tanggal', 'Nama Barang', 'Cabang Asal', 'Keperluan', 'Status', 'Qty', 'Harga Satuan', 'Total Biaya'];

            $rowCallback = function ($item) {
                $branchName = is_object($item->branch) ? ($item->branch->name ?? '-') : ($item->branch ?? '-');
                $qty =$item->quantity ?? 0;
                $price =$item->price ?? 0;
                $subtotal =$item->subtotal ?? ($qty * $price);

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? '-',
                    $branchName,
                    ucfirst($item->sub_type ?? '-'),
                    $item->status ?? '-',
                    $qty,
                    'Rp ' . number_format($price, 0, ',', '.'),
                    'Rp ' . number_format($subtotal, 0, ',', '.')
                ];
            };

            $records =$query->get();
            $sumQty =$records->sum(fn($i) =>$i->quantity ?? 0);
            $sumSubtotal =$records->sum(fn($i) =>$i->subtotal ?? (($i->quantity ?? 0) * ($i->price ?? 0)));

            $totals = [
                'colspan' => 6,
                'values' => [
                    $sumQty,
                    '',
                    'Rp ' . number_format($sumSubtotal, 0, ',', '.')
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));

        } elseif ($type == 'penjualan' \vert{}\vert{}$type == 'customer') {
            $title = 'Laporan Penjualan';$columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Customer', 'Qty', 'Harga Beli', 'Harga Jual', 'Total'];

            $rowCallback = function ($item) {
                $branchName = is_object($item->branch) ? ($item->branch->name ?? '-') : ($item->branch ?? '-');
                $qty =$item->quantity ?? 0;
                $hargaBeli =$item->purchase_price ?? 0;
                $hargaJual =$item->price ?? 0;
                $totalHarga =$item->subtotal ?? ($qty * $hargaJual);

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,$item->customer->name ?? '-',
                    $qty,
                    'Rp ' . number_format($hargaBeli, 0, ',', '.'),
                    'Rp ' . number_format($hargaJual, 0, ',', '.'),
                    'Rp ' . number_format($totalHarga, 0, ',', '.')
                ];
            };

            $records =$query->get();
            $sumQty =$records->sum(fn($i) =>$i->quantity ?? 0);
            $sumTotalHarga =$records->sum(fn($i) =>$i->subtotal ?? (($i->quantity ?? 0) * ($i->price ?? 0)));

            $totals = [
                'colspan' => 5,
                'values' => [
                    $sumQty,
                    '',
                    '',
                    'Rp ' . number_format($sumTotalHarga, 0, ',', '.')
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));

        } else {
            $title = 'Laporan Data Barang Keluar';$columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Customer', 'Qty', 'Harga Beli', 'Harga Jual', 'Total'];

            $rowCallback = function ($item) {
                $branchName = is_object($item->branch) ? ($item->branch->name ?? '-') : ($item->branch ?? '-');
                $qty =$item->quantity ?? 0;
                $hargaBeli =$item->purchase_price ?? 0;
                $hargaJual =$item->price ?? 0;
                $totalHarga =$item->subtotal ?? ($qty * $hargaJual);

                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $branchName,$item->customer->name ?? '-',
                    $qty,
                    'Rp ' . number_format($hargaBeli, 0, ',', '.'),
                    'Rp ' . number_format($hargaJual, 0, ',', '.'),
                    'Rp ' . number_format($totalHarga, 0, ',', '.')
                ];
            };

            $records =$query->get();
            $sumQty =$records->sum(fn($i) =>$i->quantity ?? 0);
            $sumTotalHarga =$records->sum(fn($i) =>$i->subtotal ?? (($i->quantity ?? 0) * ($i->price ?? 0)));

            $totals = [
                'colspan' => 5,
                'values' => [
                    $sumQty,
                    '',
                    '',
                    'Rp ' . number_format($sumTotalHarga, 0, ',', '.')
                ]
            ];

            return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback', 'totals'));
        }
    }

    public function printSuratJalan(Request $request)
    {
        $ids =$request->input('ids', []);
        $reference =$request->input('ref');

        $query = \App\Models\StockMutation::with(['product', 'customer', 'branch', 'toBranch']);

        if (!empty($ids)) {
            $query->whereIn('id',$ids);
        } elseif ($reference) {
            $query->where('reference_number',$reference);
        }

        $records =$query->get();
        $firstRecord =$records