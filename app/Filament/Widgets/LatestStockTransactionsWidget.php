<?php

namespace App\Filament\Widgets;

use App\Models\StockMutation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestStockTransactionsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $query = StockMutation::query()->latest('mutation_date')->limit(5);

        if (Auth::user()->role === 'admin_cabang') {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        return $table
            ->query($query)
            ->heading('5 Transaksi Barang Terbaru')
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('No. Referensi'),
                Tables\Columns\TextColumn::make('mutation_date')->label('Tanggal')->date(),
                Tables\Columns\TextColumn::make('product.name')->label('Nama Barang'),
                Tables\Columns\TextColumn::make('quantity')->label('Qty')->alignCenter(),
                Tables\Columns\BadgeColumn::make('sub_type')
                    ->label('Keperluan')
                    ->colors([
                        'danger' => 'produksi',
                        'warning' => 'mutasi',
                        'success' => 'penjualan',
                    ]),
            ])
            ->paginated(false);
    }
}