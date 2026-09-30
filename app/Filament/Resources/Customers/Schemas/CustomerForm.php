<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Customer')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('No Telp')
                    ->required()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Alamat Customer')
                    ->maxLength(65535),

                Textarea::make('rekening')
                    ->label('No Rekening')
                    ->maxLength(255),

            ]);
    }
}
