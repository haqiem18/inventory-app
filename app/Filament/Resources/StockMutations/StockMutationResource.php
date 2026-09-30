<?php

namespace App\Filament\Resources\StockMutations;

use App\Filament\Resources\StockMutations\Pages\CreateStockMutation;
use App\Filament\Resources\StockMutations\Pages\EditStockMutation;
use App\Filament\Resources\StockMutations\Pages\ListStockMutations;
use App\Filament\Resources\StockMutations\Schemas\StockMutationForm;
use App\Filament\Resources\StockMutations\Tables\StockMutationsTable;
use App\Models\StockMutation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockMutationResource extends Resource
{
    protected static ?string $model = StockMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownOnSquareStack;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi ';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $pluralModelLabel = 'Barang Masuk'; // Mengubah judul teks utama di halaman index/list
    protected static ?string $modelLabel = 'barang masuk';
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
        return StockMutationForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
{
    // Hanya ambil data dengan type 'Masuk'
    $query = parent::getEloquentQuery()->where('type', 'Masuk');

    $user = Auth::user(); 

    // Jika admin_cabang, batasi hanya untuk cabang mereka
    if ($user && $user->role === 'admin_cabang') {
        $query->where('branch_id', $user->branch_id);
    }

    return $query;
}

    public static function table(Table $table): Table
    {
        return StockMutationsTable::configure($table);
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
            'index' => ListStockMutations::route('/'),
            //'create' => CreateStockMutation::route('/create'),
            //'edit' => EditStockMutation::route('/{record}/edit'),
        ];
    }
}
