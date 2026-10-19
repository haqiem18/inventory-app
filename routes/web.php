<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Http\Request;

Route::get('/admin/invoice/print/{reference_number}', [InvoiceController::class, 'print'])
    ->name('invoice.print')
    ->middleware(['auth']); // Pastikan hanya yang sudah login yang bisa akses

Route::get('/admin/purchase-orders/print/{record}', [PurchaseOrderController::class, 'print'])
    ->name('purchase-orders.print')
    ->middleware(['auth']);

Route::get('/admin/purchase-orders/surat-jalan/{record}', [PurchaseOrderController::class, 'suratJalan'])
    ->name('purchase-orders.surat-jalan')
    ->middleware(['auth']);

// Gunakan satu rute ini saja yang mengarah ke Controller
Route::get('/print-table', [InvoiceController::class, 'printTable'])
    ->name('print.table')
    ->middleware(['auth']);

Route::get('/print-surat-jalan', [InvoiceController::class, 'printSuratJalan'])->name('print.surat.jalan');
Route::get('/', function () {
    return view('welcome');
});