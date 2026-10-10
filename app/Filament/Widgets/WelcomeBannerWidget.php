<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    // Memastikan Filament memanggil file view yang tepat
    protected static string $view = 'filament.widgets.welcome-banner';
}