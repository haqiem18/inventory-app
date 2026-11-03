<?php

namespace App\Filament\Resources\ReturnModels\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\ReturnModel;
use App\Models\ProductStock;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReturnModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable(),

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
                    ->sortable(),

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
                
                ActionsAction::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ReturnModel $record) => $record->status === 'Pending')
                    ->action(function (ReturnModel $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'Approved']);

                            $branchId = $record->branch_id ?? auth()->user()->branch_id;

                            foreach ($record->items as $item) {
                                $productId = $item->product_id ?? $item->item_id;

                                // Pastikan data stok di product_stocks ada, jika belum otomatis dibuat dengan stock 0
                                ProductStock::firstOrCreate(
                                    [
                                        'product_id' => $productId,
                                        'branch_id'  => $branchId,
                                    ],
                                    ['stock' => 0]
                                );

                                // Update stok menggunakan kolom 'stock'
                                if ($record->return_type === 'to_supplier') {
                                    DB::table('product_stocks')
                                        ->where('product_id', $productId)
                                        ->where('branch_id', $branchId)
                                        ->decrement('stock', $item->quantity);
                                } elseif ($record->return_type === 'customer_to_hq' || $record->return_type === 'branch_to_hq') {
                                    DB::table('product_stocks')
                                        ->where('product_id', $productId)
                                        ->where('branch_id', $branchId)
                                        ->increment('stock', $item->quantity);
                                }
                            }
                        });

                        Notification::make()
                            ->title('Retur Berhasil Disetujui & Stok Cabang Diperbarui')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make(),
                ]),
            ]);
    }
}