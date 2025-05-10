<?php

namespace App\Filament\Resources\ActivitiResource\Pages;

use App\Filament\Resources\ActivitiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivitis extends ListRecords
{
    protected static string $resource = ActivitiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New'),
        ];

    }

}
