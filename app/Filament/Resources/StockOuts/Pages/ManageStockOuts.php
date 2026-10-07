<?php

namespace App\Filament\Resources\StockOuts\Pages;

use App\Filament\Resources\StockOuts\StockOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

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
                    // Akses langsung ke tabel melalui instance livewire
                    $table = $livewire->getTable();
                    $query = $table->getQuery();

                    // Terapkan filter yang sedang aktif di tabel
                    $filters = $table->getFilters();
                    foreach ($filters as $filter) {
                        $filterState = $livewire->getTableFilterState($filter->getName());
                        if ($filterState) {
                            $filter->apply($query, $filterState);
                        }
                    }

                    // Ambil ID-nya
                    $records = $query->get();

                    return redirect()->route('print.table', [
                        'type' => 'stock-out',
                        'ids' => $records->pluck('id')->toArray()
                    ]);
                }),
                
            Actions\CreateAction::make()
                ->label('Tambah Stok Keluar')
                ->modalHeading('Tambah Stok Keluar')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    // Semua transaksi set ke PENDING dulu agar stok belum dipotong
                    $data['status'] = 'PENDING';
                    return $data;
                }),
        ];
    }
}