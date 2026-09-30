<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(255),

                TextInput::make('sku')
                    ->label('Kode SKU / Barcode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('type_id')
                    ->label('Jenis')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('brand_id')
                    ->label('Merk')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('unit_id')
                    ->label('Satuan')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('purchase_price')
                    ->label('Harga Modal Acuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),

                TextInput::make('selling_price')
                    ->label('Harga Jual Acuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required(),

                TextInput::make('min_stock')
                    ->label('Batas Minimal Stok (Alert)')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }
}