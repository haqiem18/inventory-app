<?php

namespace App\Filament\Resources\Spareparts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SparepartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),

                Select::make('product_id')
                    ->label('Nama Barang')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('mesin')
                    ->label('Mesin')
                    ->placeholder('Contoh: Mesin Indoor / Outdoor 01')
                    ->required()
                    ->maxLength(255),

                Select::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->default(fn () => Auth::user()?->branch_id)
                    ->disabled(fn () => Auth::user()?->role === 'admin_teknisi' && Auth::user()?->branch_id !== null)
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_id')
                    ->label('Teknisi')
                    ->relationship('teknisi', 'name', fn (Builder $query) => $query->whereIn('role', ['admin_teknisi', 'super_admin']))
                    ->default(fn () => Auth::id())
                    ->disabled(fn () => Auth::user()?->role === 'admin_teknisi')
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('alasan_pergantian')
                    ->label('Alasan Pergantian')
                    ->placeholder('Jelaskan alasan penggantian sparepart...')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}