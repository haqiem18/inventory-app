<?php

namespace App\Filament\Resources\Penjualans;

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

class PenjualanResource extends Resource
{
    protected static ?string $model = StockMutation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpOnSquareStack;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi Cabang ';
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $pluralModelLabel = 'Penjualan ';
    protected static ?string $modelLabel = 'Penjualan ';
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role === 'super_admin';
    }
    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'super_admin';
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
        $query->where('sub_type', 'penjualan');

        return $query;
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
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
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->state(fn($record) => $record->payment_status ?? 'hutang')
                    ->color(fn(string $state): string => match ($state) {
                        'lunas' => 'success',
                        'hutang' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'RECEIVED' => 'success',
                        'VOID' => 'danger',
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
                TextColumn::make('salesPerson.name')->label('Sales')->sortable(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Penjualans\Pages\ListPenjualans::route('/'),
            //'create' => \App\Filament\Resources\Penjualans\Pages\CreatePenjualan::route('/create'),
            //'edit' => \App\Filament\Resources\Penjualans\Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}
