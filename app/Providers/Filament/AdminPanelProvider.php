<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => Auth::user()?->branch?->name ?? 'JB Printing Apps')
            ->colors([
                'primary' => Color::Indigo,
            ])
            // TAMBAHKAN KODE INI UNTUK MEMPERCANTIK TABEL & HEADER
            ->renderHook(
                'panels::body.end',
                fn (): string => '<style>
                    /* Latar belakang header tabel agar tidak polos */
                    .fi-ta-header-cell {
                        background-color: #f1f5f9 !important; 
                        border-bottom: 2px solid #cbd5e1 !important;
                    }
                    .fi-ta-header-cell span {
                        font-weight: 600 !important;
                        color: #1e293b !important;
                    }
                    /* Efek sorot (hover) halus pada baris tabel */
                    .fi-ta-row {
                        transition: background-color 0.15s ease;
                    }
                    .fi-ta-row:hover {
                        background-color: #f8fafc !important;
                    }
                </style>'
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\WelcomeBannerWidget::class,
                \App\Filament\Widgets\StockOutBarChart::class,
                \App\Filament\Widgets\StockOutDoughnutChart::class,
                \App\Filament\Widgets\LatestStockTransactionsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}