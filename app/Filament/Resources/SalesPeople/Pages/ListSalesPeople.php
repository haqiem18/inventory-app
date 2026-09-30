<?php

namespace App\Filament\Resources\SalesPeople\Pages;

use App\Filament\Resources\SalesPeople\SalesPersonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesPeople extends ListRecords
{
    protected static string $resource = SalesPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
