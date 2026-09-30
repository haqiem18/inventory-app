<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use App\Models\Product;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('po_number')
                    ->label('No. PO')
                    ->default('PO/' . date('Ymd') . '/' . rand(100, 999))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('branch_id')
                    ->label('Cabang Pemesan')
                    ->relationship('branch', 'name')
                    ->required()
                    ->default(fn () => \App\Models\Branch::first()?->id),

                DatePicker::make('order_date')
                    ->label('Tanggal PO')
                    ->default(now())
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Sebagian Diterima',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('pending')
                    ->required(),

                Repeater::make('items')
                    ->label('Daftar Barang (Items)')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->label('Barang')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $product = Product::find($state);
                                $set('purchase_price', $product?->purchase_price ?? 0);
                            }),

                        // --- INPUT UNTUK KAIN (Roll & Kg) ---
                        TextInput::make('quantity_roll')
                            ->label('Jumlah (Roll)')
                            ->numeric()
                            ->default(1)
                            ->hidden(fn (callable $get) => !self::isFabric($get('product_id')))
                            ->required(fn (callable $get) => self::isFabric($get('product_id'))),

                        TextInput::make('quantity_kg')
                            ->label('Berat (Kg)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->hidden(fn (callable $get) => !self::isFabric($get('product_id')))
                            ->required(fn (callable $get) => self::isFabric($get('product_id')))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if (self::isFabric($get('product_id'))) {
                                    $set('subtotal', $state * ($get('purchase_price') ?? 0));
                                }
                            }),

                        // --- INPUT UNTUK BARANG BIASA (Qty Satuan) ---
                        TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->hidden(fn (callable $get) => self::isFabric($get('product_id')))
                            ->required(fn (callable $get) => !self::isFabric($get('product_id')))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if (!self::isFabric($get('product_id'))) {
                                    $set('subtotal', $state * ($get('purchase_price') ?? 0));
                                }
                            }),

                        TextInput::make('purchase_price')
                            ->label('Harga Beli (Rp)')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $qty = self::isFabric($get('product_id')) ? ($get('quantity_kg') ?? 0) : ($get('quantity') ?? 0);
                                $set('subtotal', $qty * $state);
                            }),

                        TextInput::make('subtotal')
                            ->label('Subtotal (Rp)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(5)
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    /**
     * Helper untuk mengecek apakah produk termasuk kategori Kain.
     * Sesuaikan pengecekan ini dengan kolom di database produk Anda (misal: category_id, type, atau nama).
     */
    protected static function isFabric($productId): bool
    {
        if (!$productId) return false;
        $product = Product::find($productId);
        
        // Contoh asumsi: dicek dari nama produk mengandung kata "kain" 
        // Atau jika ada kolom kategori, misal: $product->category->name === 'Kain'
        return $product && (
            stripos($product->name, 'kain') !== false || 
            stripos($product->name, 'roll') !== false
        );
    }
}