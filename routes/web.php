<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;

Route::get('/admin/invoice/print/{reference_number}', [InvoiceController::class, 'print'])
    ->name('invoice.print')
    ->middleware(['auth']); // Pastikan hanya yang sudah login yang bisa akses

// Gunakan satu rute ini saja yang mengarah ke Controller
Route::get('/print-table', [InvoiceController::class, 'printTable'])
    ->name('print.table')
    ->middleware(['auth']);

Route::get('/print-surat-jalan', [InvoiceController::class, 'printSuratJalan'])->name('print.surat.jalan');
Route::get('/', function () {
    return view('welcome');
});