<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Facades\Auth;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('order_date')
                    ->label('Tanggal PO')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang Pemesan')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'partial',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('total_amount')
                    ->label('Total Biaya')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold')
                    ->summarize(
                        Sum::make()
                            ->label('Grand Total')
                            ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan di sini
            ])
            ->recordActions([
                EditAction::make(),

                // Tombol Cetak Dokumen PO
                Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('purchase-orders.print', $record))
                    ->openUrlInNewTab(),

                // Tombol Cetak Surat Jalan PO
                Action::make('surat_jalan')
                    ->label('Surat Jalan')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn ($record) => route('purchase-orders.surat-jalan', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Bulk actions jika diperlukan
            ]);
    }
}