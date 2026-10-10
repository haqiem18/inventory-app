<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'userName' => auth()->user()->name ?? 'Superadmin',
            'stockOutUrl' => route('filament.admin.resources.stock-outs.index'),
        ];
    }

    // Menggunakan view bawaan section filament agar aman dari cache file terpisah
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.welcome-banner', [
            'userName' => auth()->user()->name ?? 'Superadmin',
            'stockOutUrl' => route('filament.admin.resources.stock-outs.index'),
        ]);
    }
}