<?php

namespace App\Filament\Resources\ReturnModels\Pages;

use App\Filament\Resources\ReturnModels\ReturnModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReturnModel extends CreateRecord
{
    protected static string $resource = ReturnModelResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
