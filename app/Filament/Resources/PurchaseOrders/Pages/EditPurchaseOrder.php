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
                $qty = $item['quantity'] ?? ($item['quantity_kg'] ?? 0);
                $price = $item['purchase_price'] ?? $item['price'] ?? 0;
                $itemSubtotal = isset($item['subtotal']) && $item['subtotal'] > 0 
                    ? (float) $item['subtotal'] 
                    : ($qty * $price);
                
                $subtotal += $itemSubtotal;
            }
        }
        
        $discount = (float) ($data['discount'] ?? 0);
        $taxPercent = 11; 
        $taxAmount = ($subtotal - $discount) * ($taxPercent / 100);
        $otherCost = (float) ($data['other_cost'] ?? 0);
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);

        $rawTotal = ($subtotal - $discount) + $taxAmount + $otherCost + $shippingCost;
        $data['total_amount'] = max(0, $rawTotal);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}