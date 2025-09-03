<?php

namespace App\Filament\Resources\RiverLevels\Pages;

use App\Filament\Resources\RiverLevels\RiverLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRiverLevel extends EditRecord
{
    protected static string $resource = RiverLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
