<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            
            // ⚡ Menampilkan Cabang
            TextColumn::make('branch.name')
                ->label('Cabang')
                ->sortable(),

            // ⚡ Menampilkan Role
            TextColumn::make('role')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'super_admin' => 'danger',
                    'admin_cabang' => 'success',
                    default => 'gray',
                }),
        ]);
    }
}