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

    protected function handleRecordCreation(array $data): StockMutation
    {
        dd($data['items'] ?? 'Items kosong atau key berbeda', $data);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new Exception('Daftar barang (items) pada repeater tidak boleh kosong.');
        }

        // Ambil item pertama untuk baris utama
        $firstItem = array_shift($items);

        $qtyFirst = $firstItem['quantity'] ?? 1;
        $priceFirst = $firstItem['purchase_price'] ?? 0;
        $subtotalFirst = $qtyFirst * $priceFirst;

        // Siapkan data untuk record utama
        $data['product_id']     = $firstItem['product_id'] ?? null;
        $data['quantity']       = $qtyFirst;
        $data['purchase_price'] = $priceFirst;
        $data['price']          = 0; // Harga jual dikosongkan untuk barang masuk
        $data['subtotal']       = $subtotalFirst;
        $data['paid_amount']    = (($data['payment_status'] ?? '') === 'lunas') ? $subtotalFirst : 0;
        $data['notes']          = $firstItem['notes'] ?? null;
        $data['status']         = 'PENDING';
        $data['type']           = 'Masuk';

        unset($data['items']);

        // Gunakan transaksi database untuk menyimpan record utama dan sisa item repeater
        return DB::transaction(function () use ($data, $items) {
            $record = static::getModel()::create($data);

            // Jika ada lebih dari 1 item di repeater, buat record tambahan
            foreach ($items as $item) {
                $qty = $item['quantity'] ?? 1;
                $price = $item['purchase_price'] ?? 0;
                $subtotal = $qty * $price;

                $extraData = $data;
                $extraData['product_id']     = $item['product_id'] ?? null;
                $extraData['quantity']       = $qty;
                $extraData['purchase_price'] = $price;
                $extraData['price']          = 0;
                $extraData['subtotal']       = $subtotal;
                $extraData['paid_amount']    = (($data['payment_status'] ?? '') === 'lunas') ? $subtotal : 0;
                $extraData['notes']          = $item['notes'] ?? null;

                static::getModel()::create($extraData);
            }

            return $record;
        });
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
