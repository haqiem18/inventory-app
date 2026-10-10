<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockMutations extends ListRecords
{
    protected static string $resource = StockMutationResource::class;

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
                
            CreateAction::make()
                ->label('Tambah Barang Masuk')
                ->modalHeading('Tambah Barang Masuk')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->createAnother(false), // Menyembunyikan tombol "Create & create another"
        ];
    }
}