<?php

namespace App\Filament\Resources\Penggunaans\Pages;

use App\Filament\Resources\Penggunaans\PenggunaanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaan extends EditRecord
{
    protected static string $resource = PenggunaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
