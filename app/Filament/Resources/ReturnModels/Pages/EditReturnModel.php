<?php

namespace App\Filament\Resources\ReturnModels\Pages;

use App\Filament\Resources\ReturnModels\ReturnModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReturnModel extends EditRecord
{
    protected static string $resource = ReturnModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
