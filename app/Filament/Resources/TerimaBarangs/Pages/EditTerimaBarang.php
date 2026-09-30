<?php

namespace App\Filament\Resources\TerimaBarangs\Pages;

use App\Filament\Resources\TerimaBarangs\TerimaBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTerimaBarang extends EditRecord
{
    protected static string $resource = TerimaBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
