<?php

namespace App\Filament\Resources\StockOuts;

use App\Models\StockMutation;
use App\Models\Branch;
use App\Models\ProductStock;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction; // Pastikan EditAction diimpor
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get as UtilitiesGet;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class StockOutResource extends Resource
{
    protected static ?string $model = StockMutation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpOnSquareStack;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi ';
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $pluralModelLabel = 'Barang Keluar';
    protected static ?string $modelLabel = 'Barang Keluar';
    
    public static function getEloquentQuery(): Builder
    {
        $query = \App\Models\StockMutation::query();

        if (auth()->check() && auth()->user()->role === 'admin_cabang') {
            $branchId = auth()->user()->branch_id;
            $query->where('branch_id', $branchId);
        }

        $query->where('type', 'OUT');

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('type')->default('OUT'),
                TextInput::make('reference_number')
                    ->label('No. Referensi')
                    ->default(fn() => 'JB-OUT-' . strtoupper(bin2hex(random_bytes(2))))
                    ->required(),

                DatePicker::make('mutation_date')
                    ->label('Tanggal Keluar')
                    ->default(now())
                    ->required(),

                Select::make('sub_type')
                    ->label('Keperluan')
                    ->options([
                        'produksi' => 'Penggunaan Produksi (Stok Keluar)',
                        'retur' => 'Retur Ke Supplier (Stok Keluar)',
                        'mutasi' => 'Mutasi ke Cabang Lain (Transfer Stok)',
                        'penjualan' => 'Penjualan ke Konsumen',
                    ])
                    ->live()
                    ->required(),

                Select::make('branch_id')
                    ->label('Cabang Asal')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('to_branch_id')
                    ->label('Ke Cabang (Tujuan)')
                    ->options(Branch::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(fn($get) => $get('sub_type') === 'mutasi')
                    ->visible(fn($get) => $get('sub_type') === 'mutasi'),

                Select::make('customer_id')
                    ->label('Pilih Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(fn($get) => $get('sub_type') === 'penjualan')
                    ->visible(fn($get) => $get('sub_type') === 'penjualan')
                    ->preload()
                    ->live(),

                Select::make('sales_id')
                    ->label('Sales / Marketing')
                    ->relationship('salesPerson', 'name')
                    ->visible(fn($get) => $get('sub_type') === 'penjualan')
                    ->required(fn($get) => $get('sub_type') === 'penjualan'),

                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'lunas' => 'Lunas',
                        'hutang' => 'Hutang',
                    ])
                    ->required(fn($get) => $get('sub_type') === 'penjualan')
                    ->visible(fn($get) => $get('sub_type') === 'penjualan')
                    ->default('lunas'),

                Select::make('product_id')
                    ->label('Nama Barang')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->relationship('product', 'name', function (Builder $query, UtilitiesGet $get) {
                        $branchId = $get('branch_id');
                        if ($branchId) {
                            $query->whereIn('id', ProductStock::where('branch_id', $branchId)->pluck('product_id'));
                        }
                    })
                    ->helperText(function (UtilitiesGet $get) {
                        $stock = ProductStock::where('product_id', $get('product_id'))
                            ->where('branch_id', $get('branch_id'))
                            ->value('stock');
                        return $get('product_id') ? "Sisa Stok: " . ($stock ?? 0) : "";
                    }),

                TextInput::make('quantity')
                    ->label('Qty Keluar')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                // Harga Beli & Jual hanya muncul/bisa diisi oleh super_admin
                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn(): bool => Auth::user()->role === 'super_admin'),

                TextInput::make('price') 
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->visible(fn(): bool => Auth::user()->role === 'super_admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->label('No. Referensi')->searchable()->sortable(),
                TextColumn::make('mutation_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('product.name')->label('Nama Barang')->searchable()
                    ->description(fn($record): string => "SKU: " . ($record->product->sku ?? '-')),
                TextColumn::make('branch.name')->label('Cabang Asal'),
                TextColumn::make('customer.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('toBranch.name')->label('Tujuan')->placeholder('-'),
                TextColumn::make('sub_type')
                    ->label('Keperluan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'produksi' => 'danger',
                        'retur' => 'danger',
                        'mutasi' => 'warning',
                        'penjualan' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'produksi' => 'Produksi',
                        'retur' => 'Retur',
                        'mutasi' => 'Mutasi Cabang',
                        'penjualan' => 'Penjualan',
                        default => ucfirst($state),
                    }),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'RECEIVED',
                        'danger' => 'VOID',
                    ]),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Qty')),

                TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),

                TextColumn::make('price')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),

                TextColumn::make('subtotal')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->summarize(Sum::make()->label('Grand Total')->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')))
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),
                TextColumn::make('salesPerson.name')->label('Sales')->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Filter Cabang')
                    ->relationship('branch', 'name')
                    ->visible(fn() => Auth::user()->role === 'super_admin'),

                SelectFilter::make('sub_type')
                    ->label('Filter Keperluan')
                    ->options([
                        'produksi' => 'Produksi',
                        'retur' => 'Retur',
                        'mutasi' => 'Mutasi',
                        'penjualan' => 'Penjualan',
                    ])
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
                // Tombol Edit di tabel (bisa dibatasi hanya untuk super_admin jika diperlukan)
                EditAction::make()
                    ->visible(fn(): bool => Auth::user()->role === 'super_admin'),

                Action::make('cetak_invoice')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn(): bool => auth()->user()->role === 'super_admin')
                    ->url(fn($record) => route('invoice.print', ['reference_number' => $record->reference_number]))
                    ->openUrlInNewTab(),

                Action::make('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin terima data ini?')
                    ->action(function ($record) {
                        $record->update(['status' => 'RECEIVED']);

                        \Filament\Notifications\Notification::make()
                            ->title('Barang berhasil diproses')
                            ->success()
                            ->send();
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

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\StockOuts\Pages\ManageStockOuts::route('/'),
            // Tambahkan rute edit di sini agar halaman edit aktif
            'edit' => \App\Filament\Resources\StockOuts\Pages\EditStockOut::route('/{record}/edit'),
        ];
    }
}