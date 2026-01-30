<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory;

use App\Domain\Inventory\Models\Stock;
use App\Filament\Resources\Inventory\Pages\ListInventory;
use App\Filament\Resources\Inventory\Pages\ViewInventory;
use App\Filament\Resources\Inventory\Schemas\InventoryInfolist;
use App\Filament\Resources\Inventory\Tables\InventoryTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class InventoryResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Inventory';

    public static function infolist(Schema $schema): Schema
    {
        return InventoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventory::route('/'),
            'view' => ViewInventory::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
