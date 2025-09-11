<?php

namespace App\Filament\Resources\RiverLevels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RiverLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                TextInput::make('continent')
                    ->required(),
                TextInput::make('length')
                    ->required()
                    ->numeric(),
                TextInput::make('current_water_level')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('normal_water_level')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('status')
                    ->required(),
                TextInput::make('current_flow_rate')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('average_flow_rate')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('temperature')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('lat')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                TextInput::make('lng')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['step' => 'any']),
                Textarea::make('description')
                    ->required(),
                DateTimePicker::make('last_updated')
                    ->required(),
                Textarea::make('weekly_data')
                    ->required(),
            ]);
    }
}
