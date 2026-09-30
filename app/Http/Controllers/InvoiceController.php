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
}
