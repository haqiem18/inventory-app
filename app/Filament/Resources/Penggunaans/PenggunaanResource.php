<?php

namespace App\Filament\Resources\Penggunaans;

use App\Models\StockMutation;
use UnitEnum;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class PenggunaanResource extends Resource
{
    protected static ?string $model = StockMutation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static UnitEnum|string|null $navigationGroup = 'Keuangan';
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $pluralModelLabel = 'Laporan Penggunaan';
    protected static ?string $modelLabel = 'Laporan Penggunaan';
    protected static ?int $navigationSort = 4;

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

            $query->where(function ($subQuery) use ($branchId) {
                $subQuery->where('branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
            });
        }

        // Menyaring data transaksi barang keluar dengan sub_type penggunaan/produksi/operasional
        $query->where('type', 'OUT')
            ->whereIn('sub_type', ['produksi', 'operasional', 'penggunaan', 'Produksi', 'Operasional']);

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
                    ->description(fn($record): string => "SKU: " . ($record->product->sku ?? '-')),
                TextColumn::make('branch.name')->label('Cabang Asal')->sortable(),

                TextColumn::make('sub_type')
                    ->label('Keperluan')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

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
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->hidden(fn(): bool => Auth::user()->role !== 'super_admin'),

                TextColumn::make('subtotal')
                    ->label('Total Biaya')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->summarize(Sum::make()->label('Grand Total')->formatStateUsing(fn($state): string => 'Rp ' . number_format($state ?? 0, 0, ',', '.')))
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
                //
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
            'index' => \App\Filament\Resources\Penggunaans\Pages\ListPenggunaans::route('/'),
        ];
    }
}