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
        // Fallback mengambil data items langsung dari form state jika tidak ada di parameter utama
        $formData = $this->form->getState();
        $items = $formData['items'] ?? $data['items'] ?? [];

        if (empty($items)) {
            throw new Exception('Daftar barang (items) pada repeater kosong. Periksa input form Anda.');
        }

        // Ambil item pertama untuk data utama record yang akan dibuat pertama kali
        $firstItem = array_shift($items);

        $qtyFirst = $firstItem['quantity'] ?? 1;
        $priceFirst = $firstItem['purchase_price'] ?? 0;
        $subtotalFirst = $qtyFirst * $priceFirst;

        $data['product_id']     = $firstItem['product_id'] ?? null;
        $data['quantity']       = $qtyFirst;
        $data['purchase_price'] = $priceFirst;
        $data['price']          = 0;
        $data['subtotal']       = $subtotalFirst;
        $data['paid_amount']    = (($data['payment_status'] ?? '') === 'lunas') ? $subtotalFirst : 0;
        $data['notes']          = $firstItem['notes'] ?? null;
        $data['status']         = 'PENDING';
        $data['type']           = 'Masuk';

        // Bersihkan key items agar tidak ikut disimpan ke kolom utama tabel
        unset($data['items']);

        // Jika ada sisa item di repeater, simpan secara transaksional di belakang
        if (!empty($items)) {
            DB::transaction(function () use ($items, $data) {
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