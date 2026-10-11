<?php

namespace App\Filament\Resources\Hutangs\Pages;

use App\Filament\Resources\Hutangs\HutangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHutang extends EditRecord
{
    protected static string $resource = HutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
