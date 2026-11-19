<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role !== 'teknisi';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role !== 'teknisi';
    }
}