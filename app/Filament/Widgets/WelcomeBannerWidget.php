<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    // Render langsung HTML-nya di sini agar tidak ada masalah cache file view
    public function render(): \Illuminate\Contracts\View\View
    {
        $userName = auth()->user()->name ?? 'Superadmin';
        $stockOutUrl = route('filament.admin.resources.stock-outs.index');

        return view('filament.widgets.welcome-banner', [
            'userName' => $userName,
            'stockOutUrl' => $stockOutUrl,
        ]);
    }
}