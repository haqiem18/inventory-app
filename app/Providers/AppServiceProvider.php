<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\StockMutation;
use App\Observers\StockMutationObserver;
use App\Models\ReturnModel; // Tambahkan ini
use App\Observers\ReturnModelObserver; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan observer mutasi stok
        StockMutation::observe(StockMutationObserver::class);

        // Daftarkan observer retur barang agar stok otomatis menyesuaikan saat Approved/Edit/Delete
        ReturnModel::observe(ReturnModelObserver::class);
    }
}