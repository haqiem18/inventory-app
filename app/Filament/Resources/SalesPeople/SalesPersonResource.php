<?php

namespace App\Filament\Resources\SalesPeople;
use App\Filament\Resources\SalesPeople\Schemas\SalesPersonForm;
use App\Filament\Resources\SalesPeople\Tables\ProductsTable;
use App\Filament\Resources\SalesPeople\Pages\CreateSalesPerson;
use App\Filament\Resources\SalesPeople\Pages\EditSalesPerson;
use App\Filament\Resources\SalesPeople\Pages\ListSalesPeople;
use App\Filament\Resources\SalesPeople\Tables\SalesPeopleTable;
use App\Models\SalesPerson;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesPersonResource extends Resource
{
    protected static ?string $model = SalesPerson::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxXMark;
    protected static UnitEnum|string|null $navigationGroup = 'Master Data';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $pluralModelLabel = 'Sales'; // Mengubah judul teks utama di halaman index/list
    protected static ?string $modelLabel = 'sales';       // Mengubah tombol "New Branch" -> "New Cabang"

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role === 'super_admin';
    }
    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'super_admin';
    }
    public static function form(Schema $schema): Schema
    {
        return SalesPersonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesPeopleTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesPeople::route('/'),
            'create' => CreateSalesPerson::route('/create'),
            'edit' => EditSalesPerson::route('/{record}/edit'),
        ];
    }
}
