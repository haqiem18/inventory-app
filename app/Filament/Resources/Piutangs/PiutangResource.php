<?php

namespace App\Filament\Resources\Piutangs;

use App\Filament\Resources\Piutangs\Pages\CreatePiutang;
use App\Filament\Resources\Piutangs\Pages\EditPiutang;
use App\Filament\Resources\Piutangs\Pages\ListPiutangs;
use App\Filament\Resources\Piutangs\Schemas\PiutangForm;
use App\Models\StockMutation;
use App\Models\DebtPayment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PiutangResource extends Resource
{
    protected static ?string $model = StockMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $pluralModelLabel = 'Laporan Piutang';
    protected static ?string $modelLabel = 'Laporan Piutang';
    public static function form(Schema $schema): Schema
    {
        return PiutangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->label('No. Nota')->sortable(),
                TextColumn::make('mutation_date')->label('Tanggal')->date(),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('product.name')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),

                // 1. KOLOM TOTAL TAGIHAN
                TextColumn::make('subtotal')
                    ->label('Total Tagihan')
                    ->state(fn($record) => $record->price * $record->quantity)
                    ->summarize(
                        Summarizer::make()
                            ->using(fn($query) => $query->sum(DB::raw('price * quantity')))
                            ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    )
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')),

                // 2. KOLOM TERBAYAR
                TextColumn::make('total_terbayar')
                    ->label('Terbayar')
                    ->state(fn($record) => $record->debtPayments()->sum('amount_paid'))
                    ->summarize(
                        Summarizer::make()
                            ->using(function ($query) {
                                $ids = $query->pluck('id');
                                return DebtPayment::whereIn('stock_mutation_id', $ids)->sum('amount_paid');
                            })
                            ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    )
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')),

                // 3. KOLOM SISA HUTANG (Gunakan kode ini agar stabil)
                TextColumn::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->state(function ($record) {
                        if ($record->payment_status === 'lunas') return 0;
                        return ($record->price * $record->quantity) - $record->debtPayments()->sum('amount_paid');
                    })
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->summarize(
                        Summarizer::make()
                            ->label('')
                            ->using(function ($query) {
                                // Ambil semua ID transaksi yang statusnya 'hutang' saja agar summary akurat
                                $query->where('payment_status', 'hutang');

                                $ids = $query->pluck('id');

                                $totalTagihan = $query->sum(DB::raw('price * quantity'));
                                $totalTerbayar = \App\Models\DebtPayment::whereIn('stock_mutation_id', $ids)->sum('amount_paid');

                                return $totalTagihan - $totalTerbayar;
                            })
                            ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    )
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn($record) => $record->payment_status ?? 'hutang')
                    ->color(fn(string $state): string => match ($state) {
                        'lunas' => 'success',
                        'hutang' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('salesPerson.name')
                    ->label('Sales')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        TextInput::make('amount_paid')
                            ->label('Jumlah Pembayaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, StockMutation $record): void {
                        $record->debtPayments()->create([
                            'amount_paid' => $data['amount_paid'],
                            'payment_date' => $data['payment_date'],
                        ]);

                        $totalTagihan = $record->price * $record->quantity;
                        $totalTerbayar = $record->debtPayments()->sum('amount_paid');

                        if ($totalTerbayar >= $totalTagihan) {
                            $record->update(['payment_status' => 'lunas']);
                        }
                        Notification::make()
                            ->title('Pembayaran Berhasil')
                            ->body('Pembayaran sebesar Rp ' . number_format($data['amount_paid'], 0, ',', '.') . ' telah dicatat.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(StockMutation $record) => $record->payment_status !== 'lunas')
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(['hutang' => 'Belum Lunas', 'lunas' => 'Lunas']),
                SelectFilter::make('sales_id')
                    ->label('Filter Sales')
                    ->relationship('salesPerson', 'name'),
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
            ]);
    }

    // Di dalam PiutangResource.php
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
        $query = parent::getEloquentQuery()->where('sub_type', 'penjualan');

        if (auth()->check()) {
            $user = auth()->user();

            // Asumsi: Anda memiliki kolom 'role' di tabel users
            // Ubah 'pusat' atau 'super_admin' sesuai dengan nilai yang tersimpan di database Anda
            $isAdminPusat = in_array($user->role, ['super_admin', 'pusat']);

            if (!$isAdminPusat) {
                $query->where('branch_id', $user->branch_id);
            }
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Piutangs\RelationManagers\DebtPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPiutangs::route('/'),
            'create' => CreatePiutang::route('/create'),
            'edit' => EditPiutang::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
