<?php

namespace App\Filament\Resources\ProductStocks\Pages;

use App\Filament\Resources\ProductStocks\ProductStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProductStocks extends ManageRecords
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
