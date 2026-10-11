<?php

namespace App\Filament\Resources\Hutangs\Pages;

use App\Filament\Resources\Hutangs\HutangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHutang extends CreateRecord
{
    protected static string $resource = HutangResource::class;
    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}
