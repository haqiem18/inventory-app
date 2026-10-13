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
            throw new Exception('Daftar barang (items) pada repeater tidak boleh kosong.');
        }

        // Ambil item pertama dari repeater
        $firstItem = array_shift($items);

        // Ambil harga dari purchase_price yang diinput user di repeater
        $priceFirst = $firstItem['purchase_price'] ?? $firstItem['price'] ?? 0;
        $qtyFirst = $firstItem['quantity'] ?? 0;
        $subtotalFirst = $qtyFirst * $priceFirst; // Perbaikan: tambahkan operator *

        $data['product_id']     = $firstItem['product_id'] ?? null;
        $data['quantity']       = $qtyFirst;
        $data['purchase_price'] = $priceFirst;
        $data['price']          = 0; // Kosongkan atau isi harga jual jika ada
        $data['subtotal']       = $subtotalFirst;
        $data['paid_amount']    = ($data['payment_status'] === 'lunas') ? $subtotalFirst : 0;
        $data['notes']          = $firstItem['notes'] ?? null;
        $data['status']         = 'PENDING';
        $data['type']           = 'Masuk';

        // Jika ada sisa item di repeater, simpan secara transaksional
        if (!empty($items)) {
            DB::transaction(function () use ($items, $data) {
                foreach ($items as $item) {
                    $price = $item['purchase_price'] ?? $item['price'] ?? 0;
                    $qty = $item['quantity'] ?? 0;
                    $subtotal = $qty * $price; // Perbaikan: tambahkan operator *

                    $extraData = array_merge($data, [
                        'product_id'     => $item['product_id'] ?? null,
                        'quantity'       => $qty,
                        'purchase_price' => $price,
                        'price'          => 0,
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