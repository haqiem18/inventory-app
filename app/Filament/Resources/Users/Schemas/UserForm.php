<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->maxLength(255),
            TextInput::make('password')
                ->password()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),

            // ⚡ Tambahan untuk Cabang
            Select::make('branch_id')
                ->relationship('branch', 'name')
                ->label('Cabang')
                ->placeholder('Pilih Cabang'),

            // ⚡ Tambahan untuk Role
            Select::make('role')
                ->options([
                    'super_admin' => 'Super Admin',
                    'admin_cabang' => 'Admin Cabang',
                ])
                ->required(),
        ]);
    }
}