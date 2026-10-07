<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    public function getTitle(): string
    {
        return 'Tambah Cabang';
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