<?php

namespace App\Filament\Resources\ReturnModels\Pages;

use App\Filament\Resources\ReturnModels\ReturnModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReturnModels extends ListRecords
{
    protected static string $resource = ReturnModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
