<?php

namespace App\Filament\Resources\TerimaBarangs\Pages;

use App\Filament\Resources\TerimaBarangs\TerimaBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTerimaBarangs extends ListRecords
{
    protected static string $resource = TerimaBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
