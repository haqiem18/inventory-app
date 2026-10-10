<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    // Arahkan ke nama file baru agar tidak nyangkut di cache lama
    protected string $view = 'filament.widgets.custom-welcome';
    
    protected int | string | array $columnSpan = 'full';
}