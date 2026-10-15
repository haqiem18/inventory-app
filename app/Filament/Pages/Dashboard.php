<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Gunakan fungsi override navigasi langsung
    public static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationLabel(): string
    {
        return __('filament-panels::pages/dashboard.title');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2'; // Memaksa ikon diganti secara langsung
    }
}