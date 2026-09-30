<?php

namespace App\Filament\Resources\Hutangs\Pages;

use App\Filament\Resources\Hutangs\HutangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHutangs extends ListRecords
{
    protected static string $resource = HutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
