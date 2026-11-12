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
        // Pastikan status otomatis jadi 'PENDING' jika kosong/null
        if (empty($mutation->status)) {
            $mutation->status = 'PENDING';
        }

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

        // 3. Jika mutasi sudah RECEIVED lalu data diubah (quantity/product/to_branch_id)
        if ($mutation->status === 'RECEIVED' && $mutation->wasChanged(['type', 'quantity', 'product_id', 'branch_id', 'to_branch_id'])) {
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
        $isRevert     = ($action === 'revert');
        $product_id   = $isRevert ? $mutation->getOriginal('product_id') : $mutation->product_id;
        $branch_id    = $isRevert ? $mutation->getOriginal('branch_id') : $mutation->branch_id;
        $to_branch_id = $isRevert ? $mutation->getOriginal('to_branch_id') : $mutation->to_branch_id;
        $quantity     = $isRevert ? $mutation->getOriginal('quantity') : $mutation->quantity;
        $type         = $isRevert ? $mutation->getOriginal('type') : $mutation->type;

        if (empty($product_id) || empty($branch_id)) return;

        if ($type === 'Masuk') {
            $stock = ProductStock::firstOrCreate(
                ['product_id' => $product_id, 'branch_id' => $branch_id], 
                ['stock' => 0]
            );
            
            $stockChange = $isRevert ? -$quantity : $quantity;
            $stock->increment('stock', $stockChange);

            if ($action === 'apply') {
                StockBatch::firstOrCreate(
                    ['stock_mutation_id' => $mutation->id],
                    [
                        'product_id'         => $product_id,
                        'quantity_initial'   => $quantity,
                        'quantity_remaining' => $quantity,
                        'purchase_price'     => $mutation->purchase_price,
                        'status'             => 'active',
                    ]
                );
            } else {
                StockBatch::where('stock_mutation_id', $mutation->id)->delete();
            }
        } elseif ($type === 'OUT') {
            // 1. Kurangi/Kembalikan stok di cabang asal
            $origin = ProductStock::firstOrCreate(
                ['product_id' => $product_id, 'branch_id' => $branch_id],
                ['stock' => 0]
            );
            
            if ($isRevert) {
                $origin->increment('stock', $quantity);
                $this->processFifo($product_id, -$quantity);
            } else {
                $origin->decrement('stock', $quantity);
                $this->processFifo($product_id, $quantity);
            }
            
            // 2. Tambah/Kurangi stok di cabang tujuan (to_branch_id) jika ada
            if (!empty($to_branch_id)) {
                $destStock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $product_id,
                        'branch_id'  => $to_branch_id
                    ],
                    ['stock' => 0]
                );

                if ($action === 'apply') {
                    $destStock->increment('stock', $quantity);
                } else { 
                    $destStock->decrement('stock', $quantity);
                }
            }
        }
    }

    private function processFifo($product_id, $qtyChange)
    {
        if ($qtyChange > 0) { // Barang Keluar (Mengurangi FIFO)
            $batches = StockBatch::where('product_id', $product_id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')->get();

            foreach ($batches as $batch) {
                if ($qtyChange <= 0) break;
                $deduct = min($batch->quantity_remaining, $qtyChange);
                $batch->decrement('quantity_remaining', $deduct);
                if ($batch->quantity_remaining <= 0) {
                    $batch->update(['status' => 'exhausted']);
                }
                $qtyChange -= $deduct;
            }
        } else { // Revert Barang Keluar (Mengembalikan kapasitas FIFO)
            $absQty = abs($qtyChange);
            $batches = StockBatch::where('product_id', $product_id)
                ->where('status', 'exhausted')
                ->orderBy('created_at', 'desc')->get();

            foreach ($batches as $batch) {
                if ($absQty <= 0) break;
                
                $capacitySpace = $batch->quantity_initial - $batch->quantity_remaining;
                $add = min($capacitySpace, $absQty);
                
                if ($add > 0) {
                    $batch->increment('quantity_remaining', $add);
                    if ($batch->quantity_remaining > 0) {
                        $batch->update(['status' => 'active']);
                    }
                    $absQty -= $add;
                }
            }
        }
    }
}