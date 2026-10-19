<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Setiap kali item disimpan (create / update)
        static::saved(function ($item) {
            $item->updatePurchaseOrderTotal();
        });

        // Setiap kali item dihapus
        static::deleted(function ($item) {
            $item->updatePurchaseOrderTotal();
        });
    }

    /**
     * Hitung total subtotal dan update ke purchase_orders.total_amount
     */
    public function updatePurchaseOrderTotal(): void
    {
        if ($this->purchase_order_id) {
            $total = static::where('purchase_order_id', $this->purchase_order_id)
                ->sum('subtotal');

            PurchaseOrder::where('id', $this->purchase_order_id)
                ->update(['total_amount' => $total]);
        }
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}