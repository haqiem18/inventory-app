<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 1;
    protected string $view = 'filament.widgets.welcome-banner';
    protected int | string | array $columnSpan = 'full';
}