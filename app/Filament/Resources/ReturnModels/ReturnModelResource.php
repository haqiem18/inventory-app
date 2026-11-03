<?php

namespace App\Filament\Resources\ReturnModels;

use App\Filament\Resources\ReturnModels\Pages\CreateReturnModel;
use App\Filament\Resources\ReturnModels\Pages\EditReturnModel;
use App\Filament\Resources\ReturnModels\Pages\ListReturnModels;
use App\Filament\Resources\ReturnModels\Schemas\ReturnModelForm;
use App\Filament\Resources\ReturnModels\Tables\ReturnModelsTable;
use App\Models\ReturnModel;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReturnModelResource extends Resource
{
    protected static ?string $model = ReturnModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUturnLeft;
    protected static UnitEnum|string|null $navigationGroup = 'Operasional';
    protected static ?string $navigationLabel = 'Retur Barang';
    protected static ?string $modelLabel = 'Retur Barang';
    protected static ?string $pluralModelLabel = 'Retur Barang';

    public static function form(Schema $schema): Schema
    {
        return ReturnModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Sesuaikan hak akses jika ingin dibatasi hanya untuk super_admin, atau biarkan true jika semua bisa akses
        return Auth::user()->role === 'super_admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'super_admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturnModels::route('/'),
            'create' => CreateReturnModel::route('/create'),
            'edit' => EditReturnModel::route('/{record}/edit'),
        ];
    }
}
