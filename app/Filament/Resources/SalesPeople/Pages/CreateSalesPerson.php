<?php

namespace App\Filament\Resources\SalesPeople\Pages;

use App\Filament\Resources\SalesPeople\SalesPersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesPerson extends CreateRecord
{
    protected static string $resource = SalesPersonResource::class;
}
