<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal = isset($item['subtotal']) ? (float) $item['subtotal'] : 0;
                $total += $subtotal;
            }
        }
        
        $data['total_amount'] = $total;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}