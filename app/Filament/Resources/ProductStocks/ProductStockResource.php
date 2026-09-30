<?php

namespace App\Filament\Resources\ProductStocks;

use App\Filament\Resources\ProductStocks\Pages\ManageProductStocks;
use App\Models\ProductStock;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ProductStockResource extends Resource
{
    protected static ?string $model = ProductStock::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    protected static ?string $navigationLabel = 'Stok Barang';
    protected static ?string $pluralModelLabel = 'Stok Barang';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stock')
                    ->label('Jumlah Stok')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Barang')
                    ->searchable(['name', 'sku'])
                    ->description(fn($record): string => "SKU: {$record->product->sku}")
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Lokasi Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    // ⚡ Tambahkan baris ini untuk menampilkan total otomatis
                    ->summarize(
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Stok')
                    )
                    ->color(fn(int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Filter Cabang')
                    ->relationship('branch', 'name')
                    ->visible(fn() => Auth::user()->role === 'super_admin'),
            ])
            ->actions([
                Action::make('riwayat')
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modalHeading(fn($record) => "Riwayat Mutasi: {$record->product->name} ({$record->branch->name})")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($record) {
                        // Proteksi akses
                        if (Auth::user()->role === 'admin_cabang' && $record->branch_id !== Auth::user()->branch_id) {
                            return "Anda tidak memiliki akses ke riwayat cabang ini.";
                        }

                        $mutations = \App\Models\StockMutation::with(['branch', 'toBranch'])
                            ->where('product_id', $record->product_id)
                            ->where(function ($query) use ($record) {
                                $query->where('branch_id', $record->branch_id)
                                    ->orWhere('to_branch_id', $record->branch_id);
                            })
                            ->orderBy('mutation_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

                        // Proteksi jika data kosong
                        if ($mutations->isEmpty()) {
                            return "Belum ada riwayat mutasi untuk barang ini.";
                        }

                        return view('filament.components.stock-history-table', [
                            'mutations' => $mutations,
                            'currentBranchId' => $record->branch_id,
                        ]);
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['product', 'branch']);

        $user = Auth::user();

        if ($user && $user->role === 'admin_cabang') {
            return $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }
}
