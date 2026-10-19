<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenjualans extends ListRecords
{
    protected static string $resource = PenjualanResource::class;

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
                        'type' => 'customer', // <--- Ubah jadi 'piutang'
                        'ids' => $records->pluck('id')->toArray()
                    ]);
                }),
        ];
    }
}
