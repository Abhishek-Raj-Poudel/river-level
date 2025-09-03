<?php

namespace App\Filament\Resources\RiverLevels\Pages;

use App\Filament\Resources\RiverLevels\RiverLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiverLevels extends ListRecords
{
    protected static string $resource = RiverLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
