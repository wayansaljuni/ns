<?php

namespace App\Filament\Resources\ActivitiResource\Pages;

use App\Filament\Resources\ActivitiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateActiviti extends CreateRecord
{
    protected static string $resource = ActivitiResource::class;
    protected function getRedirectUrl(): string
    {
        return url('admin/activitis'); // Redirect ke halaman index SPK Activities
    }
}

