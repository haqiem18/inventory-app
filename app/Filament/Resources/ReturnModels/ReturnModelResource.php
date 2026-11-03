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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReturnModelResource extends Resource
{
    protected static ?string $model = ReturnModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUturnLeft;
    protected static UnitEnum|string|null $navigationGroup = 'Operasional';
    protected static ?string $navigationLabel = 'Retur Barang';
    protected static ?string $modelLabel = 'Retur Barang';
    protected static ?string $pluralModelLabel = 'Retur Barang';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Jika user login adalah admin_cabang, batasi hanya menampilkan retur miliknya/cabangnya
        if (Auth::check() && Auth::user()->role === 'admin_cabang') {
            $branchId = Auth::user()->branch_id;
            // Sesuaikan kolom relasi cabang di tabel returns jika ada, misal 'branch_id' atau 'partner_id'
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

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
        // Izinkan super_admin dan admin_cabang melihat menu di sidebar
        return Auth::check() && in_array(Auth::user()->role, ['super_admin', 'admin_cabang']);
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['super_admin', 'admin_cabang']);
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