<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                TextInput::make('name')
                    ->label('Nama Cabang')
                    ->required()
                    ->maxLength(255),
                    
                Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(65535),
                    
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'pusat' => 'Pusat',
                        'cabang' => 'Cabang',
                    ])
                    ->required()
                    ->default('cabang'),
            ]);
    }
}