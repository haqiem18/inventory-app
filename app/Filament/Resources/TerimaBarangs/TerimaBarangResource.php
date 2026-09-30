<?php

namespace App\Filament\Resources\TerimaBarangs;

use App\Models\StockMutation;
use App\Models\Branch;
use App\Models\ProductStock;
use UnitEnum;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class TerimaBarangResource extends Resource
{
    protected static ?string $model = StockMutation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpOnSquareStack;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi ';
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $pluralModelLabel = 'Terima Barang';
    protected static ?string $modelLabel = 'Terima Barang';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role === 'admin_cabang';
    }
    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'admin_cabang';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = \App\Models\StockMutation::query();

        if (auth()->check() && auth()->user()->role === 'admin_cabang') {
            $branchId = auth()->user()->branch_id;

            // Admin cabang bisa melihat jika:
            // 1. Mereka yang mengeluarkan barang (branch_id)
            // 2. ATAU mereka yang menerima barang (to_branch_id)
            $query->where(function ($subQuery) use ($branchId) {
                $subQuery->where('branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
            });
        }

        // Pastikan tetap memfilter hanya yang tipe 'OUT'
        $query->where('sub_type', 'mutasi');

        return $query;
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('sub_type')->default('penjualan'),
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

                TextInput::make('price')
                    ->label('Harga Satuan (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->label('No. Referensi')->searchable()->sortable(),
                TextColumn::make('mutation_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('product.name')->label('Nama Barang')->searchable()
                    ->description(fn($record): string => "SKU: {$record->product->sku}"),
                TextColumn::make('branch.name')->label('Cabang Asal'),

                TextColumn::make('toBranch.name') // Menggunakan relasi ke model Branch
                    ->label('Tujuan')
                    ->placeholder('-'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'RECEIVED',
                    ]),
                TextColumn::make('sub_type')
                    ->label('Keperluan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'produksi' => 'gray',
                        'mutasi' => 'info',
                        'penjualan' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'produksi' => 'Produksi',
                        'mutasi' => 'Mutasi Cabang',
                        'penjualan' => 'Penjualan',
                        default => ucfirst($state),
                    }), // Di dalam method table() -> columns()
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'RECEIVED' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Qty')),

                TextColumn::make('price')
                    ->label('Harga Satuan (Rp)')
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TerimaBarangs\Pages\ListTerimaBarangs::route('/'),
            //'create' => \App\Filament\Resources\TerimaBarangs\Pages\CreateTerimaBarang::route('/create'),
            //'edit' => \App\Filament\Resources\TerimaBarangs\Pages\EditTerimaBarang::route('/{record}/edit'),
        ];
    }
}
