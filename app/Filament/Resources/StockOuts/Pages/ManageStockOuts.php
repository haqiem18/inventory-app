<?php

namespace App\Filament\Resources\StockOuts\Pages;

use App\Filament\Resources\StockOuts\StockOutResource;
use App\Models\StockMutation;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;
use Exception;

class ManageStockOuts extends ManageRecords
{
    protected static string $resource = StockOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Tabel')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function ($livewire) {
                    $table = $livewire->getTable();
                    $query = $table->getQuery();

                    $filters = $table->getFilters();
                    foreach ($filters as $filter) {
                        $filterState = $livewire->getTableFilterState($filter->getName());
                        if ($filterState) {
                            $filter->apply($query, $filterState);
                        }
                    }

                    $records = $query->get();

                    return redirect()->route('print.table', [
                        'type' => 'OUT',
                        'ids' => $records->pluck('id')->toArray()
                    ]);
                }),
                
            Actions\CreateAction::make()
                ->label('Tambah Stok Keluar')
                ->modalHeading('Tambah Stok Keluar')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->createAnother(false)
                ->using(function (array $data, string $model): StockMutation {
                    $items = $data['items'] ?? [];

                    if (empty($items)) {
                        throw new Exception('Daftar barang pada repeater tidak boleh kosong.');
                    }

                    $firstItem = array_shift($items);

                    $qtyFirst = $firstItem['quantity'] ?? 1;
                    $priceFirst = $firstItem['price'] ?? 0;
                    $purchasePriceFirst = $firstItem['purchase_price'] ?? 0;
                    $subtotalFirst = $qtyFirst * $priceFirst;

                    $data['product_id']     = $firstItem['product_id'] ?? null;
                    $data['quantity']       = $qtyFirst;
                    $data['price']          = $priceFirst;
                    $data['purchase_price'] = $purchasePriceFirst;
                    $data['subtotal']       = $subtotalFirst;
                    $data['status']         = 'PENDING';
                    $data['type']           = 'OUT';

                    unset($data['items']);

                    return DB::transaction(function () use ($model, $data, $items) {
                        $record = $model::create($data);

                        foreach ($items as $item) {
                            $qty = $item['quantity'] ?? 1;
                            $price = $item['price'] ?? 0;
                            $purchasePrice = $item['purchase_price'] ?? 0;
                            $subtotal = $qty * $price;

                            $extraData = $data;
                            $extraData['product_id']     = $item['product_id'] ?? null;
                            $extraData['quantity']       = $qty;
                            $extraData['price']          = $price;
                            $extraData['purchase_price'] = $purchasePrice;
                            $extraData['subtotal']       = $subtotal;

                            $model::create($extraData);
                        }

                        return $record;
                    });
                }),
        ];
    }

    // Menangani pengisian data ke form saat tombol Edit diklik
    protected function configureEditAction(Actions\EditAction $action): void
    {
        $action->mutateRecordDataUsing(function (array $data, StockMutation $record): array {
            // Ambil semua baris yang memiliki reference_number yang sama
            $relatedItems = StockMutation::where('reference_number', $record->reference_number)->get();

            $items = [];
            foreach ($relatedItems as $item) {
                $items[] = [
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'purchase_price' => $item->purchase_price,
                    'price'          => $item->price,
                ];
            }

            $data['items'] = $items;
            return $data;
        })->using(function (StockMutation $record, array $data): StockMutation {
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception('Daftar barang pada repeater tidak boleh kosong.');
            }

            return DB::transaction(function () use ($record, $data, $items) {
                // Hapus semua baris lama dengan reference_number ini untuk kemudian di-insert ulang
                StockMutation::where('reference_number', $record->reference_number)->delete();

                $firstItem = array_shift($items);
                $qtyFirst = $firstItem['quantity'] ?? 1;
                $priceFirst = $firstItem['price'] ?? 0;
                $purchasePriceFirst = $firstItem['purchase_price'] ?? 0;
                $subtotalFirst = $qtyFirst * $priceFirst;

                $data['product_id']     = $firstItem['product_id'] ?? null;
                $data['quantity']       = $qtyFirst;
                $data['price']          = $priceFirst;
                $data['purchase_price'] = $purchasePriceFirst;
                $data['subtotal']       = $subtotalFirst;
                $data['type']           = 'OUT';
                unset($data['items']);

                // Buat record utama yang baru
                $updatedRecord = StockMutation::create($data);

                // Buat baris-baris berikutnya jika ada
                foreach ($items as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $purchasePrice = $item['purchase_price'] ?? 0;
                    $subtotal = $qty * $price;

                    $extraData = $data;
                    $extraData['product_id']     = $item['product_id'] ?? null;
                    $extraData['quantity']       = $qty;
                    $extraData['price']          = $price;
                    $extraData['purchase_price'] = $purchasePrice;
                    $extraData['subtotal']       = $subtotal;

                    StockMutation::create($extraData);
                }

                return $updatedRecord;
            });
        });
    }
}