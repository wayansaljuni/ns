<?php

namespace App\Filament\Resources\ActivitiResource\Pages;

use App\Filament\Resources\ActivitiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActiviti extends EditRecord
{
    protected static string $resource = ActivitiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
