<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Models\StockMutation;

Route::get('/admin/invoice/print/{reference_number}', [InvoiceController::class, 'print'])
    ->name('invoice.print')
    ->middleware(['auth']); // Pastikan hanya yang sudah login yang bisa akses

Route::get('/print-table', function (Illuminate\Http\Request $request) {
    $ids = $request->query('ids');
    $records = StockMutation::whereIn('id', $ids)->get();
    
    return view('invoices.print-table', compact('records'));
})->name('print.table');

Route::get('/', function () {
    return view('welcome');
});
