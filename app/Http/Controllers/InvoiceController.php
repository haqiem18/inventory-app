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

        // Ambil data berdasarkan array ID tanpa with() yang berisiko relasi belum ada
        $query = StockMutation::whereIn('id', $ids);

        if ($type == 'piutang') {
            $title = 'Laporan Piutang';
            $columns = ['No. Nota', 'Tanggal', 'Customer', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Piutang', 'Status', 'Sales'];
            $rowCallback = function ($item) {
                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->customer->name ?? $item->customer_name ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->paid_amount ?? 0, 0, ',', '.'),
                    'Rp ' . number_format(($item->subtotal ?? 0) - ($item->paid_amount ?? 0), 0, ',', '.'),
                    $item->payment_status ?? '-',
                    $item->sales->name ?? $item->sales_name ?? '-'
                ];
            };
        } elseif ($type == 'hutang') {
            $title = 'Laporan Hutang';
            $columns = ['No. Nota', 'Tanggal', 'Supplier', 'Nama Barang', 'Total Tagihan', 'Terbayar', 'Sisa Hutang', 'Status'];
            $rowCallback = function ($item) {
                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->supplier->name ?? $item->supplier_name ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->paid_amount ?? 0, 0, ',', '.'),
                    'Rp ' . number_format(($item->subtotal ?? 0) - ($item->paid_amount ?? 0), 0, ',', '.'),
                    $item->payment_status ?? '-'
                ];
            };
        } elseif ($type == 'stock-in' || $type == 'barang-masuk') {
            $title = 'Laporan Data Barang Masuk';
            $columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Supplier', 'Qty', 'Harga Beli', 'Total Harga'];
            $rowCallback = function ($item) {
                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $item->branch ?? '-',
                    $item->supplier->name ?? '-',
                    $item->quantity ?? 0,
                    'Rp ' . number_format($item->purchase_price ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->subtotal ?? (($item->quantity ?? 0) * ($item->purchase_price ?? 0)), 0, ',', '.')
                ];
            };
        } else {
            $title = 'Laporan Data Barang Keluar';
            $columns = ['No. Ref', 'Tanggal', 'Barang', 'Cabang', 'Customer', 'Qty', 'Harga Jual', 'Total'];
            $rowCallback = function ($item) {
                return [
                    $item->reference_number ?? '-',
                    $item->mutation_date ?? '-',
                    $item->product->name ?? $item->nama_barang ?? '-',
                    $item->branch ?? '-',
                    $item->customer->name ?? '-',
                    $item->quantity ?? 0,
                    'Rp ' . number_format($item->price ?? 0, 0, ',', '.'),
                    'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.')
                ];
            };
        }

        $records = $query->get();

        return view('invoices.print-report', compact('title', 'columns', 'records', 'rowCallback'));
    }
}
