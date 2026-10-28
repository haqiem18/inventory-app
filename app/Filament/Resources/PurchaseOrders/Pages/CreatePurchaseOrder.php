<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Ambil subtotal per item, atau hitung dari qty * price jika subtotal kosong
                $qty = $item['quantity'] ?? ($item['quantity_kg'] ?? 0);
                $price = $item['purchase_price'] ?? $item['price'] ?? 0;
                $itemSubtotal = isset($item['subtotal']) && $item['subtotal'] > 0 
                    ? (float) $item['subtotal'] 
                    : ($qty * $price); // Perbaikan operator perkalian (*)
                
                $subtotal += $itemSubtotal;
            }
        }
        
        // Hitung komponen pajak dan biaya tambahan secara aman
        $discount = (float) ($data['discount'] ?? 0);
        $taxPercent = 11; // PPN 11%
        $taxAmount = ($subtotal - $discount) * ($taxPercent / 100); // Perbaikan operator minus (-)
        $otherCost = (float) ($data['other_cost'] ?? 0);
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);

        // Total amount resmi yang disimpan ke database sudah termasuk PPN & pembulatan/biaya lain
        $data['total_amount'] = ($subtotal - $discount) + $taxAmount + $otherCost + $shippingCost; // Perbaikan operator minus (-)

        return $data;
    }

    public function getTitle(): string
    {
        return 'Tambah PO';
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

    protected function getRedirectUrl(): string
    {
        // Mengarahkan kembali ke halaman index/tabel setelah berhasil simpan
        return $this->getResource()::getUrl('index');
    }
}