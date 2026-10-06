<?php

namespace App\Filament\Resources\Piutangs\Pages;

use App\Filament\Resources\Piutangs\PiutangResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePiutang extends CreateRecord
{
    protected static string $resource = PiutangResource::class;
    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}
