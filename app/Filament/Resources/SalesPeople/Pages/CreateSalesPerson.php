<?php

namespace App\Filament\Resources\SalesPeople\Pages;

use App\Filament\Resources\SalesPeople\SalesPersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesPerson extends CreateRecord
{
    protected static string $resource = SalesPersonResource::class;
    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}
