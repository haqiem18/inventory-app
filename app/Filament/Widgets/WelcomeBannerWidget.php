<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function render(): View
    {
        $userName = auth()->user()->name ?? 'Superadmin';
        $stockOutUrl = route('filament.admin.resources.stock-outs.index');

        return view('filament.widgets.welcome-banner', [
            'userName' => $userName,
            'stockOutUrl' => $stockOutUrl,
        ]);
    }
}