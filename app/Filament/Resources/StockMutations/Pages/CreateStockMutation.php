<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Models\StockMutation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        if (empty($items)) {
            throw new Exception('Daftar barang (items) tidak boleh kosong.');
        }

        // Ambil item pertama untuk dikembalikan ke Filament agar record utama berhasil dibuat
        $firstItem = array_shift($items);
        $subtotalFirst = ($firstItem['quantity'] ?? 0) * ($firstItem['purchase_price'] ?? 0);

        $data['product_id']     = $firstItem['product_id'];
        $data['quantity']       = $firstItem['quantity'];
        $data['purchase_price'] = $firstItem['purchase_price'];
        $data['subtotal']       = $subtotalFirst;
        $data['paid_amount']    = ($data['payment_status'] === 'lunas') ? $subtotalFirst : 0;
        $data['notes']          = $firstItem['notes'] ?? null;
        $data['status']         = 'PENDING';
        $data['type']           = 'Masuk';

        // Jika ada sisa item lebih dari 1, simpan secara manual di sini
        if (!empty($items)) {
            DB::transaction(function () use ($items, $data) {
                foreach ($items as $item) {
                    $subtotal = ($item['quantity'] ?? 0) * ($item['purchase_price'] ?? 0);
                    
                    $extraData = array_merge($data, [
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'subtotal'       => $subtotal,
                        'paid_amount'    => ($data['payment_status'] === 'lunas') ? $subtotal : 0,
                        'notes'          => $item['notes'] ?? null,
                    ]);

                    StockMutation::create($extraData);
                }
            });
        }

        return $data;
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