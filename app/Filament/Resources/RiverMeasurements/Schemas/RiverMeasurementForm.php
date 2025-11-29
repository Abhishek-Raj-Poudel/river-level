<?php

namespace App\Filament\Resources\RiverMeasurements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RiverMeasurementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('river_level_id')
                    ->relationship('riverLevel', 'name')
                    ->required(),
                TextInput::make('water_level')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('measured_at')
                    ->required(),
            ]);
    }
}
