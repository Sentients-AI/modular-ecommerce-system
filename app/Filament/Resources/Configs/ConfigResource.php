<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configs;

use App\Domain\Config\Models\SystemConfig;
use App\Filament\Resources\Configs\Pages\ListConfigs;
use App\Filament\Resources\Configs\Pages\ViewConfig;
use App\Filament\Resources\Configs\Schemas\ConfigInfolist;
use App\Filament\Resources\Configs\Tables\ConfigsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class ConfigResource extends Resource
{
    protected static ?string $model = SystemConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Configuration';

    protected static ?string $modelLabel = 'Configuration';

    protected static ?string $pluralModelLabel = 'Configuration';

    public static function infolist(Schema $schema): Schema
    {
        return ConfigInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfigs::route('/'),
            'view' => ViewConfig::route('/{record}'),
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
