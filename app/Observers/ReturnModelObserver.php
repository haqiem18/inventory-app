<?php

namespace App\Observers;

use App\Models\ReturnModel;
use App\Models\StockMutation;

class ReturnModelObserver
{
    public function updated(ReturnModel $return): void
    {
        // 1. Jika status berubah menjadi 'Approved'
        if ($return->wasChanged('status') && $return->status === 'Approved') {
            $this->applyMutations($return);
        } 
        // 2. Jika data retur yang sudah Approved diubah (misalnya quantity atau itemnya)
        elseif ($return->status === 'Approved' && $return->wasChanged()) {
            // Hapus mutasi lama berdasarkan nomor referensi, lalu buat ulang
            StockMutation::where('notes', 'like', 'Retur: ' . $return->reference_number)->delete();
            $this->applyMutations($return);
        }
    }

    public function deleted(ReturnModel $return): void
    {
        // Jika data retur yang sudah Approved dihapus, hapus catatan mutasinya
        // (Observer StockMutation akan otomatis merevert stok kembali secara aman)
        if ($return->status === 'Approved') {
            $mutations = StockMutation::where('notes', 'like', 'Retur: ' . $return->reference_number)->get();
            foreach ($mutations as $mutation) {
                $mutation->delete();
            }
        }
    }

    private function applyMutations(ReturnModel $return)
    {
        $branchId = $return->branch_id ?? auth()->user()->branch_id ?? 1;

        foreach ($return->items as $item) {
            $productId = $item->product_id ?? $item->item_id;
            
            // Tentukan tipe mutasi berdasarkan jenis retur
            $mutationType = ($return->return_type === 'to_supplier') ? 'OUT' : 'Masuk';

            // Buat record StockMutation (Otomatis memicu StockMutationObserver untuk update stok & FIFO)
            StockMutation::create([
                'product_id'     => $productId,
                'branch_id'      => $branchId,
                'type'           => $mutationType,
                'quantity'       => $item->quantity,
                'purchase_price' => $item->price ?? 0,
                'status'         => 'RECEIVED',
                'notes'          => 'Retur: ' . $return->reference_number,
            ]);
        }
    }
}