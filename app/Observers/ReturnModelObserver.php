<?php

namespace App\Observers;

use App\Models\ReturnModel;
use App\Models\ProductStock;

class ReturnModelObserver
{
    public function updated(ReturnModel $return): void
    {
        // Jika statusnya disetujui (Approved) atau item/jumlahnya diubah saat status Approved
        if ($return->status === 'Approved') {
            $branchId = $return->branch_id ?? 1;

            foreach ($return->items as $item) {
                $productId = $item->product_id ?? $item->item_id;
                
                $productStock = ProductStock::firstOrCreate(
                    ['product_id' => $productId, 'branch_id' => $branchId],
                    ['stock' => 0]
                );

                // Hitung perubahan berdasarkan jenis retur
                // Anda bisa mencatat nilai lama (original) vs nilai baru jika quantity diubah
                $originalQuantity = $item->getOriginal('quantity') ?? $item->quantity;
                $qtyDifference = $item->quantity - $originalQuantity;

                if ($qtyDifference !== 0) {
                    if ($return->return_type === 'to_supplier') {
                        // Jika retur ke supplier bertambah quantity-nya, stok harus semakin berkurang
                        $productStock->decrement('stock', $qtyDifference);
                    } elseif ($return->return_type === 'customer_to_hq' || $return->return_type === 'branch_to_hq') {
                        // Jika retur ke pusat bertambah quantity-nya, stok harus semakin bertambah
                        $productStock->increment('stock', $qtyDifference);
                    }
                }
            }
        }
    }

    public function deleted(ReturnModel $return): void
    {
        // Jika data retur yang sudah Approved dihapus, kembalikan stok seperti semula (revert)
        if ($return->status === 'Approved') {
            $branchId = $return->branch_id ?? 1;

            foreach ($return->items as $item) {
                $productId = $item->product_id ?? $item->item_id;
                $productStock = ProductStock::where(['product_id' => $productId, 'branch_id' => $branchId])->first();

                if ($productStock) {
                    if ($return->return_type === 'to_supplier') {
                        $productStock->increment('stock', $item->quantity);
                    } elseif ($return->return_type === 'customer_to_hq' || $return->return_type === 'branch_to_hq') {
                        $productStock->decrement('stock', $item->quantity);
                    }
                }
            }
        }
    }
}