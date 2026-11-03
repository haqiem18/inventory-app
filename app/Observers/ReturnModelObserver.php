<?php

namespace App\Observers;

use App\Models\ReturnModel;
use App\Models\StockMutation;
use Illuminate\Support\Carbon;

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
            $quantity = $item->quantity;
            $purchasePrice = $item->price ?? 0;

            // Buat record StockMutation dengan menyertakan mutation_date
            StockMutation::create([
                'reference_number' => 'MUT-' . $return->reference_number . '-' . $productId,
                'product_id'       => $productId,
                'branch_id'        => $branchId,
                'type'             => $mutationType,
                'quantity'         => $quantity,
                'purchase_price'   => $purchasePrice,
                'subtotal'         => $quantity * $purchasePrice,
                'mutation_date'    => Carbon::now()->toDateString(), // Mengisi tanggal mutasi hari ini
                'status'           => 'RECEIVED',
                'notes'            => 'Retur: ' . $return->reference_number,
            ]);
        }
    }
}