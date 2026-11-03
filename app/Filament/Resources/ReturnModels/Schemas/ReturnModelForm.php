<?php

namespace App\Filament\Resources\ReturnModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use App\Models\Product;
use App\Models\SalesPerson; // Sesuaikan dengan model sales/supplier Anda
use App\Models\Supplier;

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
                    ->native(false)
                    ->reactive(),

                TextInput::make('reference_number')
                    ->label('No. Referensi / Nota')
                    ->required()
                    ->default('RET-' . date('YmdHis'))
                    ->maxLength(100),

                Select::make('partner_id')
                    ->label('Pihak Terkait (Supplier / Cabang / Customer)')
                    ->options(function (callable $get) {
                        $type = $get('return_type');
                        
                        // Menyesuaikan pilihan berdasarkan jenis retur
                        if ($type === 'to_supplier') {
                            // Mengambil data sales/supplier sesuai aturan kolom nama sales
                            return Supplier::pluck('name', 'id');
                        } elseif ($type === 'branch_to_hq') {
                            return \App\Models\Branch::pluck('name', 'id');
                        } else {
                            return \App\Models\Customer::pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

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
                        Select::make('item_id')
                            ->label('Barang')
                            ->options(Product::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),
                        
                        TextInput::make('quantity')
                            ->label('Jumlah (Qty)')
                            ->numeric()
                            ->required()
                            ->default(1),

                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}