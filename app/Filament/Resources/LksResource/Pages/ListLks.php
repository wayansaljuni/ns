<?php

namespace App\Filament\Resources\LksResource\Pages;

use App\Filament\Resources\LksResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLks extends ListRecords
{
    protected static string $resource = LksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
