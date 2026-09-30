<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\StockMutation; // Tambahkan ini
use App\Observers\StockMutationObserver; // Tambahkan ini

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
        // Daftarkan observer di sini agar stok otomatis berkurang/bertambah
        StockMutation::observe(StockMutationObserver::class);
    }
}