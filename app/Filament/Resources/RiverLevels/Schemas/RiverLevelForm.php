<?php

namespace App\Filament\Resources\RiverLevels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
/* use Filament\Forms\Components\Section; */
/* use Filament\Forms\Components\Grid; */
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;

class RiverLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                ComponentsSection::make('Basic Information')
                    ->description('General details about the river and monitoring station')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('River Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('station_name')
                            ->label('Monitoring Station')
                            ->maxLength(255)
                            ->placeholder('e.g., Central Station')
                            ->columnSpan(1),

                        TextInput::make('district')
                            ->label('District')
                            ->maxLength(255)
                            ->placeholder('e.g., Kathmandu')
                            ->columnSpan(1),

                        TextInput::make('scrape_link')
                            ->label('Scrape Link')
                            ->url()
                            ->placeholder('https://example.com/scrape-data')
                            ->columnSpan(1),

                        Select::make('continent')
                            ->required()
                            ->options([
                                'Africa' => 'Africa',
                                'Antarctica' => 'Antarctica',
                                'Asia' => 'Asia',
                                'Europe' => 'Europe',
                                'North America' => 'North America',
                                'Oceania' => 'Oceania',
                                'South America' => 'South America',
                            ])
                            ->searchable()
                            ->columnSpan(1),

                        TextInput::make('country')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('length')
                            ->label('River Length')
                            ->required()
                            ->numeric()
                            ->suffix('km')
                            ->minValue(0)
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(3)
                    ->collapsible(),

                ComponentsSection::make('Water Level Monitoring')
                    ->description('Current and normal water level measurements')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(3)
                    ->schema([
                        TextInput::make('current_water_level')
                            ->label('Current Level')
                            ->required()
                            ->numeric()
                            ->suffix('m')
                            ->extraAttributes(['step' => '0.01'])
                            ->minValue(0)
                            ->columnSpan(1),

                        TextInput::make('normal_water_level')
                            ->label('Normal Level')
                            ->required()
                            ->numeric()
                            ->suffix('m')
                            ->extraAttributes(['step' => '0.01'])
                            ->minValue(0)
                            ->columnSpan(1),

                        Select::make('status')
                            /* ->required() */
                            ->options([
                                'normal' => 'Normal',
                                'low' => 'Low',
                                'high' => 'High',
                                'warning' => 'Warning',
                                'critical' => 'Critical',
                            ])
                            ->native(false)
                            ->columnSpan(1)
                            ->disabled(),

                    ])
                    ->columnSpan(3)
                    ->collapsible(),

                ComponentsSection::make('Environmental Data')
                    ->description('Temperature and location information')
                    ->icon('heroicon-o-map-pin')
                    ->columns(3)
                    ->schema([
                        TextInput::make('temperature')
                            ->label('Water Temperature')
                            ->required()
                            ->numeric()
                            ->suffix('°C')
                            ->extraAttributes(['step' => '0.1'])
                            ->columnSpan(1),

                        TextInput::make('lat')
                            ->label('Latitude')
                            ->required()
                            ->numeric()
                            ->extraAttributes(['step' => 'any'])
                            ->placeholder('e.g., 27.7172')
                            ->minValue(-90)
                            ->maxValue(90)
                            ->columnSpan(1),

                        TextInput::make('lng')
                            ->label('Longitude')
                            ->required()
                            ->numeric()
                            ->extraAttributes(['step' => 'any'])
                            ->placeholder('e.g., 85.3240')
                            ->minValue(-180)
                            ->maxValue(180)
                            ->columnSpan(1),

                        DateTimePicker::make('last_updated')
                            ->label('Last Updated')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(3)
                    ->collapsible(),

            ]);
    }
}
