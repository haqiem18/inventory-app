<?php

namespace App\Filament\Resources\Penggunaans\Pages;

use App\Filament\Resources\Penggunaans\PenggunaanResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListPenggunaans extends ListRecords
{
    protected static string $resource = PenggunaanResource::class;

    public function getTitle(): string
    {
        return 'Laporan Penggunaan';
    }

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

                    // Perbaikan penulisan array parameter rute cetak untuk Laporan Penggunaan
                    return redirect()->route('print.table', [
                        'type' => 'penggunaan',
                        'ids' => $records->pluck('id')->toArray()
                    ]);
                }),
        ];
    }
}