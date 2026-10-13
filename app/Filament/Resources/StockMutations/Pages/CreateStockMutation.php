<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Models\StockMutation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    // Ambil alih proses create sepenuhnya agar tidak melakukan insert data kosong di level root
    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        
        if (empty($items)) {
            throw new Exception('Daftar barang (items) pada repeater tidak boleh kosong.');
        }

        // Siapkan data header / utama yang sama untuk setiap baris barang
        $baseData = [
            'reference_number' => $data['reference_number'] ?? null,
            'mutation_date'    => $data['mutation_date'] ?? now(),
            'branch_id'        => $data['branch_id'] ?? null,
            'supplier_id'      => $data['supplier_id'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? 'pembelian',
            'payment_status'   => $data['payment_status'] ?? 'lunas',
            'type'             => 'Masuk',
            'status'           => 'PENDING',
        ];

        $createdRecord = null;

        // Simpan setiap baris item dari repeater ke dalam database secara transaksional
        DB::transaction(function () use ($items, $baseData, &$createdRecord) {
            foreach ($items as $item) {
                $subtotal = ($item['quantity'] ?? 0) * ($item['purchase_price'] ?? 0);
                
                $mutationData = array_merge($baseData, [
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'subtotal'       => $subtotal,
                    'paid_amount'    => ($baseData['payment_status'] === 'lunas') ? $subtotal : 0,
                    'notes'          => $item['notes'] ?? null,
                ]);

                $createdRecord = StockMutation::create($mutationData);
            }
        });

        // Kembalikan record terakhir agar Filament bisa melakukan redirect dengan benar
        return $createdRecord;
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