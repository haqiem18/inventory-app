<?php

namespace App\Filament\Resources\ReturnModels\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\ReturnModel;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Notifications\Notification;

class ReturnModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Tambahkan Kolom Barang / Produk di sini
                TextColumn::make('items.product.name')
                    ->label('Barang / Produk')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList(),

                TextColumn::make('return_type')
                    ->label('Jenis Retur')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'to_supplier' => 'Retur ke Supplier',
                        'branch_to_hq' => 'Retur Cabang ke Pusat',
                        'customer_to_hq' => 'Retur Konsumen ke Pusat',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'to_supplier' => 'danger',
                        'branch_to_hq' => 'warning',
                        'customer_to_hq' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_amount')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('return_type')
                    ->label('Filter Jenis Retur')
                    ->options([
                        'to_supplier' => 'Retur ke Supplier',
                        'branch_to_hq' => 'Retur Cabang ke Pusat',
                        'customer_to_hq' => 'Retur Konsumen ke Pusat',
                    ]),
            ])
            ->actions([
                ActionsEditAction::make(),
                
                // Tombol Approve
                ActionsAction::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ReturnModel $record) => $record->status === 'Pending')
                    ->action(function (ReturnModel $record) {
                        $record->update(['status' => 'Approved']);

                        Notification::make()
                            ->title('Retur Berhasil Disetujui, Stok & Riwayat Mutasi Diperbarui')
                            ->success()
                            ->send();
                    }),

                // Tombol Cetak Surat Jalan Retur (Konsisten dengan PO)
                ActionsAction::make('surat_jalan')
                    ->label('Surat Jalan')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (ReturnModel $record) => route('return-models.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make(),
                ]),
            ]);
    }
}