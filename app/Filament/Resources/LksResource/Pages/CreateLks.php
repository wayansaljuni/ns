<?php

namespace App\Filament\Resources\LksResource\Pages;

use App\Filament\Resources\LksResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLks extends CreateRecord
{
    protected static string $resource = LksResource::class;
    protected function getRedirectUrl(): string
    {
        return url('admin/lks'); // Redirect ke halaman index SPK Activities
    }

}
