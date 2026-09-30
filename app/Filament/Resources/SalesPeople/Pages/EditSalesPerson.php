<?php

namespace App\Filament\Resources\SalesPeople\Pages;

use App\Filament\Resources\SalesPeople\SalesPersonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesPerson extends EditRecord
{
    protected static string $resource = SalesPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
