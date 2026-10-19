<?php

namespace App\Filament\Resources\Penggunaans\Pages;

use App\Filament\Resources\Penggunaans\PenggunaanResource;
use Filament\Resources\Pages\ListRecords;

class ListPenggunaans extends ListRecords
{
    protected static string $resource = PenggunaanResource::class;

    public function getTitle(): string
    {
        return 'Laporan Penggunaan';
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}