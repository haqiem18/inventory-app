<?php

namespace App\Observers;

use App\Models\StockMutation;
use App\Models\ProductStock;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;

class StockMutationObserver
{
    protected static $processing = false;

    public function creating(StockMutation $mutation): void
    {
        $mutation->subtotal = $mutation->quantity * $mutation->purchase_price;
        if ($mutation->payment_status === 'lunas') {
            $mutation->paid_amount = $mutation->subtotal;
        }
    }

    public function updating(StockMutation $mutation): void
    {
        $mutation->subtotal = $mutation->quantity * $mutation->purchase_price;
        if ($mutation->payment_status === 'lunas') {
            $mutation->paid_amount = $mutation->subtotal;
        }
    }

    public function created(StockMutation $mutation): void
    {
        // Hanya proses jika saat pertama kali dibuat statusnya langsung RECEIVED
        if ($mutation->status === 'RECEIVED') {
            $this->processStock($mutation, 'apply');
        }
    }

    public function updated(StockMutation $mutation): void
    {
        if (self::$processing) return;

        // 1. Pemicu utama: Status berubah menjadi RECEIVED
        if ($mutation->wasChanged('status') && $mutation->status === 'RECEIVED') {
            self::$processing = true;
            $this->processStock($mutation, 'apply');
            return;
        }

        // 2. Tambahan: Pemicu untuk VOID (Revert stok)
        if ($mutation->wasChanged('status') && $mutation->status === 'VOID') {
            self::$processing = true;
            $this->processStock($mutation, 'revert');
            return;
        }

        // 3. Jika mutasi sudah RECEIVED lalu data diubah (quantity/product)
        if ($mutation->status === 'RECEIVED' && $mutation->wasChanged(['type', 'quantity', 'product_id', 'branch_id'])) {
            self::$processing = true;
            DB::transaction(function () use ($mutation) {
                $this->processStock($mutation, 'revert');
                $this->processStock($mutation, 'apply');
            });
        }
    }

    public function deleted(StockMutation $mutation): void
    {
        if ($mutation->status === 'RECEIVED') {
            $this->processStock($mutation, 'revert');
        }
    }

    private function processStock(StockMutation $mutation, $action)
    {
        $isRevert = ($action === 'revert');
        $product_id = $isRevert ? $mutation->getOriginal('product_id') : $mutation->product_id;
        $branch_id  = $isRevert ? $mutation->getOriginal('branch_id') : $mutation->branch_id;
        $quantity   = $isRevert ? $mutation->getOriginal('quantity') : $mutation->quantity;
        $type       = $isRevert ? $mutation->getOriginal('type') : $mutation->type;
        $multiplier = $isRevert ? -1 : 1;

        if (empty($product_id) || empty($branch_id)) return;

        if ($type === 'Masuk') {
            $stock = ProductStock::firstOrCreate(['product_id' => $product_id, 'branch_id' => $branch_id], ['stock' => 0]);
            $stock->increment('stock', $quantity * $multiplier);

            if ($action === 'apply') {
                StockBatch::create([
                    'stock_mutation_id'  => $mutation->id,
                    'product_id'         => $product_id,
                    'quantity_initial'   => $quantity,
                    'quantity_remaining' => $quantity,
                    'purchase_price'     => $mutation->purchase_price,
                    'status'             => 'active',
                ]);
            } else {
                StockBatch::where('stock_mutation_id', $mutation->id)->delete();
            }
        } elseif ($type === 'OUT') {
            $origin = ProductStock::where('product_id', $product_id)->where('branch_id', $branch_id)->first();
            if ($origin) {
                $origin->increment('stock', ($quantity * $multiplier) * -1);
            }
            $this->processFifo($product_id, $quantity * $multiplier);
        }
    }

    private function processFifo($product_id, $qtyChange)
    {
        if ($qtyChange > 0) { // Barang Keluar
            $batches = StockBatch::where('product_id', $product_id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')->get();

            foreach ($batches as $batch) {
                if ($qtyChange <= 0) break;
                $deduct = min($batch->quantity_remaining, $qtyChange);
                $batch->decrement('quantity_remaining', $deduct);
                if ($batch->quantity_remaining <= 0) $batch->update(['status' => 'exhausted']);
                $qtyChange -= $deduct;
            }
        } else { // Revert Barang Keluar
            $absQty = abs($qtyChange);
            $batches = StockBatch::where('product_id', $product_id)
                ->where('status', 'exhausted')
                ->orderBy('created_at', 'desc')->get();

            foreach ($batches as $batch) {
                if ($absQty <= 0) break;
                $add = min($batch->quantity_initial - $batch->quantity_remaining, $absQty);
                $batch->increment('quantity_remaining', $add);
                $batch->update(['status' => 'active']);
                $absQty -= $add;
            }
        }
    }
}
