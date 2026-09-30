<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Merk')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true) // Memicu update otomatis setelah selesai mengetik nama
                    ->afterStateUpdated(fn (string $operation, $state, $set) => 
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->disabled() // Dikunci agar user tidak bisa edit manual
                    ->dehydrated(), // Tetap dikirim ke database saat disave meskipun di-disabled
            ]);
    }
}