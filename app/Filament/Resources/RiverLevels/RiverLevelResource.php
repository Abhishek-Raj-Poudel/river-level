<?php

namespace App\Filament\Resources\RiverLevels;

use App\Filament\Resources\RiverLevels\Pages\CreateRiverLevel;
use App\Filament\Resources\RiverLevels\Pages\EditRiverLevel;
use App\Filament\Resources\RiverLevels\Pages\ListRiverLevels;
use App\Filament\Resources\RiverLevels\Schemas\RiverLevelForm;
use App\Filament\Resources\RiverLevels\Tables\RiverLevelsTable;
use App\Models\RiverLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RiverLevelResource extends Resource
{
    protected static ?string $model = RiverLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RiverLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RiverLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RiverMeasurementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRiverLevels::route('/'),
            'create' => CreateRiverLevel::route('/create'),
            'edit' => EditRiverLevel::route('/{record}/edit'),
        ];
    }
}
