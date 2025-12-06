<?php

namespace App\Filament\Widgets;

use App\Models\Admin;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Podcasts count', Podcast::count())
                ->icon(Heroicon::Microphone),
            Stat::make('Episodes count', Episode::count())
                ->icon(Heroicon::Signal),
            Stat::make('Admins count', Admin::count())
                ->icon(Heroicon::Users),
        ];
    }
}
