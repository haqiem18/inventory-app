<?php

namespace App\Filament\Resources\Hutangs\Pages;

use App\Filament\Resources\Hutangs\HutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHutangs extends ListRecords
{
    protected static string $resource = HutangResource::class;

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
                        'type' => 'hutang', // <--- Ubah jadi 'piutang'
                        'ids' => $records->pluck('id')->toArray()
                    ]);
                }),
        ];
    }
}
