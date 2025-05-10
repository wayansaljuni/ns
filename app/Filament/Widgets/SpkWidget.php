<?php

namespace App\Filament\Widgets;

use App\Models\Activiti;
use App\Models\Lks;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SpkWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth()->user();
        // dd($user);
   
        if ($user->hasRole('lks')) {
            return [
                Stat::make('LKS : Open', Lks::where('status','=', 'Open')->count())
                ->description('Create Lks ?')
                ->descriptionIcon('heroicon-o-wrench-screwdriver',IconPosition::Before)
                ->url(route('filament.admin.resources.lks.create'))
                ->Color('info'),

                Stat::make('LKS : Closed', Lks::where('status', 'close')->count())
                ->description('LKS Activities...')
                ->descriptionIcon('heroicon-o-clipboard-document-list',IconPosition::Before)
                ->url(route('filament.admin.resources.lks.index'))
                ->Color('success'),
            ];
        }    
        return [
            Stat::make('Spk Activities : Open', Activiti::where('status','=', 'Open')->count())
            ->description('Create Activities ?')
            ->descriptionIcon('heroicon-o-wrench-screwdriver',IconPosition::Before)
            ->url(route('filament.admin.resources.activitis.create'))
            ->Color('info'),

            Stat::make('Spk Activities : Closed', Activiti::where('status', 'closed')->count())
            ->description('Teknisi Activities...')
            ->descriptionIcon('heroicon-o-clipboard-document-list',IconPosition::Before)
            ->url(route('filament.admin.resources.activitis.create'))
            ->Color('success'),
        ];

    }
}
