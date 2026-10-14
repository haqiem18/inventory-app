<?php

namespace App\Filament\Resources\StockMutations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter; // Tambahkan ini
use Filament\Forms\Components\DatePicker; // Tambahkan ini
use Illuminate\Database\Eloquent\Builder; // Tambahkan ini untuk tipe Builder
use Illuminate\Support\Facades\Auth;

class StockMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ... (kolom tetap sama seperti sebelumnya)
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
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')) // Biarkan ini menangani format
                    ->sortable()
                    // Ganti formatStateUsing dengan hidden()
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),

                TextColumn::make('subtotal')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->summarize(Sum::make()->label('Grand Total')->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')))
                    // Ganti formatStateUsing dengan hidden()
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),
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
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['to'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['to'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])

            ->recordActions([
                //EditAction::make(),
                //DeleteAction::make(),
                Action::make('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // Tombol hanya muncul jika status PENDING
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin terima data ini?')
                    ->action(function ($record, $livewire) {
                        // Proses update status
                        $record->update(['status' => 'RECEIVED']);

                        \Filament\Notifications\Notification::make()
                            ->title('Barang berhasil diproses')
                            ->success()
                            ->send();

                        // Opsional: Refresh tabel agar tombol langsung hilang
                        $livewire->dispatch('refreshComponent');
                    }),

                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    // Tombol hanya muncul jika statusnya RECEIVED (atau sesuai kebijakan Anda)
                    // Jika ingin bisa VOID yang statusnya PENDING juga, ubah kondisinya
                    ->visible(fn($record) => $record->status === 'RECEIVED')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.')
                    ->action(function ($record, $livewire) {
                        // Observer akan otomatis menjalankan processStock('revert') 
                        // karena kita mengubah status ke VOID
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
