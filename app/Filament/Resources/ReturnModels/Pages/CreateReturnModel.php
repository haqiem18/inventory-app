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
    public function getTitle(): string
    {
        return 'Tambah Retur';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),

            $this->getCreateAnotherFormAction()
                ->label('Simpan & Buat Lagi'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
}
