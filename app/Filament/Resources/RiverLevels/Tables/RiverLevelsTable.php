<?php

namespace App\Filament\Resources\RiverLevels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiverLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('station_name')
                    ->searchable(),
                TextColumn::make('station_link')
                    ->label('Station Link')
                    ->url(fn ($record) => $record->station_link)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TextColumn::make('country')
                    ->searchable(),
                TextColumn::make('elevation')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' m')
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('current_water_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('normal_water_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('temperature')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
