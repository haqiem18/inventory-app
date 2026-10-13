<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Models\StockMutation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    // Kita override handleRecordCreation dan bypass return parent agar tidak melakukan insert kosong
    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        if (empty($items)) {
            throw new Exception('Daftar barang (items) tidak boleh kosong.');
        }

        $baseData = array_merge($data, [
            'status' => 'PENDING',
            'type' => 'Masuk',
        ]);

        $createdRecord = null;

        DB::transaction(function () use ($items, $baseData, &$createdRecord) {
            foreach ($items as $item) {
                $subtotal = ($item['quantity'] ?? 0) * ($item['purchase_price'] ?? 0);
                
                $mutationData = array_merge($baseData, [
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'subtotal'       => $subtotal,
                    'paid_amount'    => ($baseData['payment_status'] === 'lunas') ? $subtotal : 0,
                    'notes'          => $item['notes'] ?? null,
                ]);

                $createdRecord = StockMutation::create($mutationData);
            }
        });

        return $createdRecord;
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