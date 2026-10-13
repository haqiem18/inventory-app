<?php

namespace App\Filament\Resources\StockMutations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Group;
use Filament\Forms\Get;

class StockMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- INFORMASI UTAMA FAKTUR (HEADER) ---
                Group::make()->schema([
                    TextInput::make('reference_number')
                        ->label('No. Referensi / Nota')
                        ->required()
                        ->default(fn() => 'JB-IN-'. strtoupper(bin2hex(random_bytes(2))))
                        ->unique(ignoreRecord: true),

                    DatePicker::make('mutation_date')
                        ->label('Tanggal Masuk')
                        ->required()
                        ->default(now()),

                    Select::make('branch_id')
                        ->label('Tujuan Cabang')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('transaction_type')
                        ->label('Tipe Transaksi')
                        ->options([
                            'pembelian' => 'Pembelian',
                            'mutasi' => 'Mutasi Antar Cabang',
                        ])
                        ->default('pembelian')
                        ->required()
                        ->live(),

                    Select::make('payment_status')
                        ->label('Status Pembayaran')
                        ->options([
                            'lunas' => 'Lunas',
                            'hutang' => 'Hutang',
                        ])
                        ->visible(fn(Get $get) => $get('transaction_type') === 'pembelian')
                        ->required(fn(Get $get) => $get('transaction_type') === 'pembelian'),
                ])->columns(2),

                Hidden::make('type')
                    ->default('Masuk'),

                // --- REPEATER DETAIL BARANG (BISA BANYAK) ---
                Repeater::make('items')
                    ->label('Daftar Barang Masuk')
                    ->schema([
                        Select::make('product_id')
                            ->label('Pilih Barang')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('quantity')
                            ->label('Jumlah Masuk')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('purchase_price')
                            ->label('Harga Beli')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->columnSpan(3),

                        TextInput::make('notes')
                            ->label('Keterangan')
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->defaultItems(1)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}