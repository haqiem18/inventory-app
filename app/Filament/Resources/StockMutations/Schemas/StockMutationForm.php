<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Models\StockMutation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Ambil data items dari repeater
        $items = $data['items'] ?? [];
        
        // Hapus key items agar tidak error saat create ke model StockMutation
        unset($data['items']);

        $data['status'] = 'PENDING';
        $data['type'] = 'Masuk';

        $createdRecord = null;

        // Jika items kosong (antisipasi), buat 1 data kosong
        if (empty($items)) {
            return static::getModel()::create($data);
        }

        // Looping setiap baris barang dari repeater dan simpan dengan reference_number yang sama
        foreach ($items as $item) {
            $mutationData = array_merge($data, [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'notes' => $item['notes'] ?? null,
            ]);

            $createdRecord = static::getModel()::create($mutationData);
        }

        return $createdRecord;
    }

    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Barang Masuk';
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