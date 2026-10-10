<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    // Tambahkan kata kunci 'static' di sini
    protected static string $view = 'filament.widgets.welcome-banner';
    
    protected int | string | array $columnSpan = 'full';
}