<?php

namespace App\Filament\Resources\Spareparts\Pages;

use App\Filament\Resources\Spareparts\SparepartResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSparepart extends CreateRecord
{
    protected static string $resource = SparepartResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()?->role === 'teknisi') {
            $data['user_id'] = Auth::id();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Penggantian Sparepart';
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