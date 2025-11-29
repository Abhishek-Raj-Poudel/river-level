<?php

namespace App\Filament\Resources\RiverMeasurements\Pages;

use App\Filament\Resources\RiverMeasurements\RiverMeasurementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiverMeasurements extends ListRecords
{
    protected static string $resource = RiverMeasurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
