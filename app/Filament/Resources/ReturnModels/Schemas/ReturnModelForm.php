<?php

namespace App\Filament\Resources\ReturnModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class ReturnModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('return_type')
                    ->label('Jenis Retur')
                    ->options([
                        'to_supplier' => 'Retur ke Supplier',
                        'branch_to_hq' => 'Retur Cabang ke Pusat',
                        'customer_to_hq' => 'Retur Konsumen ke Pusat',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('reference_number')
                    ->label('Nomor Referensi / Nota')
                    ->required()
                    ->default('RET-' . date('YmdHis'))
                    ->maxLength(100),

                TextInput::make('partner_id')
                    ->label('ID Pihak Terkait (Supplier/Customer/Cabang)')
                    ->numeric()
                    ->nullable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Completed' => 'Selesai',
                    ])
                    ->default('Pending')
                    ->required(),

                Textarea::make('reason')
                    ->label('Alasan Retur')
                    ->columnSpanFull()
                    ->rows(3),

                Repeater::make('items')
                    ->relationship('items')
                    ->label('Daftar Barang Retur')
                    ->schema([
                        TextInput::make('item_id')
                            ->label('ID Barang / Produk')
                            ->numeric()
                            ->required(),
                        
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1),

                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}