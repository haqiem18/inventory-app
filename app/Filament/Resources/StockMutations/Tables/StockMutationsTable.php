<?php

namespace App\Filament\Resources\StockMutations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mutation_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Barang')
                    ->searchable(['name', 'sku'])
                    ->description(fn($record): string => "SKU: {$record->product->sku}")
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Qty')),

                TextColumn::make('purchase_price')
                    ->label('Harga Beli (Rp)')
                    ->getStateUsing(fn ($record) => $record->purchase_price ?? $record->purchase_price ?? 0)
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->hidden(fn (): bool => Auth::user()->role !== 'super_admin'),

                TextColumn::make('subtotal')
                    ->label('Total Harga')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->summarize(Sum::make()->label('Grand Total')->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.')))
                    ->hidden(fn (): bool => Auth::user()->role !== 'super_admin'),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Filter Cabang')
                    ->relationship('branch', 'name')
                    ->visible(fn() => Auth::user()->role === 'super_admin'),

                Filter::make('mutation_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('to')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('mutation_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('mutation_date', '<=', $date),
                            );
                    })
            ])
            ->recordActions([
                Action::make('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin terima data ini?')
                    ->action(function ($record, $livewire) {
                        $record->update(['status' => 'RECEIVED']);

                        \Filament\Notifications\Notification::make()
                            ->title('Barang berhasil diproses')
                            ->success()
                            ->send();

                        $livewire->dispatch('refreshComponent');
                    }),

                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'RECEIVED')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.')
                    ->action(function ($record, $livewire) {
                        $record->update(['status' => 'VOID']);

                        \Filament\Notifications\Notification::make()
                            ->title('Transaksi berhasil dibatalkan')
                            ->success()
                            ->send();

                        $livewire->dispatch('refreshComponent');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}