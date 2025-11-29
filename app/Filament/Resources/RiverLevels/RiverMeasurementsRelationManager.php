<?php

namespace App\Filament\Resources\RiverLevels;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class RiverMeasurementsRelationManager extends RelationManager
{
    protected static string $relationship = 'measurements';

    protected static ?string $recordTitleAttribute = 'measured_at';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('measured_at')
            ->defaultSort('measured_at', 'desc')
            ->columns([
                TextColumn::make('measured_at')
                    ->label('Date & Time')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                TextColumn::make('water_level')
                    ->label('Water Level')
                    ->numeric(2)
                    ->suffix(' m')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('recent')
                    ->label('Last 7 days')
                    ->query(fn ($query) => $query->where('measured_at', '>=', now()->subDays(7))),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
