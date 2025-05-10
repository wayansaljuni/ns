<?php

namespace App\Filament\Resources\LksResource\Pages;

use App\Filament\Resources\LksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLks extends EditRecord
{
    protected static string $resource = LksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
