<?php

namespace App\Filament\Resources\RiverLevels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RiverLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('river_name')
                    ->required(),
                TextInput::make('lat')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('lng')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('threshold')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
            ]);
    }
}
