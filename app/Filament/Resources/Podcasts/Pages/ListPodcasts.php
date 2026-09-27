<?php

namespace App\Filament\Resources\Podcasts\Pages;

use App\Filament\Actions\ImportPodcast;
use App\Filament\Actions\UpdatePodcasts;
use App\Filament\Resources\Podcasts\PodcastResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodcasts extends ListRecords
{
    protected static string $resource = PodcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UpdatePodcasts::make(),
            ImportPodcast::make(),
            CreateAction::make(),
        ];
    }
}
