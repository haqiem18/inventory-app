<?php

namespace App\Filament\Resources\StockMutations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;


class StockMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                Select::make('product_id')
                    ->label('Pilih Barang')
                    ->relationship('product', 'name')
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
                    ->live(), // Filament v3 menggunakan 'live()' untuk memicu update form secara real-time

                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'lunas' => 'Lunas',
                        'hutang' => 'Hutang',
                    ])
                    ->visible(fn($get) => $get('transaction_type') === 'pembelian')
                    ->required(fn($get) => $get('transaction_type') === 'pembelian'),

                TextInput::make('quantity')
                    ->label('Jumlah Masuk')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),

                Textarea::make('notes')
                    ->label('Keterangan / Catatan')
                    ->rows(3),
                Hidden::make('type')
                    ->default('Masuk'),
            ]);
    }
}
