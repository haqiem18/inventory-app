<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemSubtotal = isset($item['subtotal']) ? (float) $item['subtotal'] : 0;
                $subtotal += $itemSubtotal;
            }
        }
        
        // Hitung komponen pajak PPN 11% dan biaya lainnya
        $discount = $data['discount'] ?? 0;
        $taxPercent = 11; 
        $taxAmount = ($subtotal - $discount) * ($taxPercent / 100);
        $otherCost = $data['other_cost'] ?? 0;
        $shippingCost = $data['shipping_cost'] ?? 0;

        // Total amount resmi yang disimpan ke database sudah termasuk PPN
        $data['total_amount'] = ($subtotal - $discount) + $taxAmount + $otherCost + $shippingCost;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}