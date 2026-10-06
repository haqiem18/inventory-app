<?php

namespace App\Filament\Resources\TerimaBarangs\Pages;

use App\Filament\Resources\TerimaBarangs\TerimaBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTerimaBarang extends CreateRecord
{
    protected static string $resource = TerimaBarangResource::class;
    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}
