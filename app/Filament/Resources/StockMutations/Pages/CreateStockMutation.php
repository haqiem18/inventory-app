<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $baseData = array_merge($data, [
            'status' => 'PENDING',
            'type' => 'Masuk',
        ]);

        $createdRecord = null;

        // Looping setiap baris barang dari repeater
        foreach ($items as $item) {
            $mutationData = array_merge($baseData, [
                'product_id'     => $item['product_id'],
                'quantity'       => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'subtotal'       => $item['quantity'] * $item['purchase_price'],
                'notes'          => $item['notes'] ?? null,
            ]);

            if ($baseData['payment_status'] === 'lunas') {
                $mutationData['paid_amount'] = $mutationData['subtotal'];
            }

            $createdRecord = static::getModel()::create($mutationData);
        }

        // Kembalikan record terakhir agar Filament bisa redirect dengan benar
        return $createdRecord ?? static::getModel()->first();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Barang Masuk';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Simpan'),
            $this->getCreateAnotherFormAction()->label('Simpan & Buat Lagi'),
            $this->getCancelFormAction()->label('Batal'),
        ];
    }
}