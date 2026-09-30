<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Supplier')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('No Telp')
                    ->required()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(65535),

            ]);
    }
}
