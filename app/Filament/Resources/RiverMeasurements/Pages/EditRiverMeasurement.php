<?php

namespace App\Filament\Resources\RiverMeasurements\Pages;

use App\Filament\Resources\RiverMeasurements\RiverMeasurementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRiverMeasurement extends EditRecord
{
    protected static string $resource = RiverMeasurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
