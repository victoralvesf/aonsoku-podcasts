<?php

namespace App\Filament\Actions;

use App\Jobs\ProcessPodcast;
use App\Models\Podcast;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ImportPodcast extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Import Podcast')
            ->button()
            ->icon(Heroicon::CloudArrowDown)
            ->color('gray')
            ->modalWidth(Width::Large)
            ->schema([
                TextInput::make('url')
                    ->label('Feed URL')
                    ->required(),
            ])
            ->action(function (array $data) {
                $feedUrl = $data['url'];

                self::setImportCache();
                self::importPodcastAction($feedUrl);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'import-podcast';
    }

    protected static function importPodcastAction(string $feedUrl): void
    {
        $cacheKey = self::getImportCacheKey();

        try {
            Bus::batch([new ProcessPodcast(null, $feedUrl)])
                ->finally(function () use ($cacheKey) {
                    Cache::forget($cacheKey);
                })
                ->dispatch();

            Notification::make()
                ->title('Podcast import started')
                ->body('The podcast will be available shortly.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Podcast import cannot be started')
                ->body('More details: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function getImportCacheKey(): string
    {
        return Podcast::IMPORT_CACHE_KEY . auth()->id();
    }

    protected static function setImportCache(): void
    {
        Cache::put(self::getImportCacheKey(), true, now()->addMinutes(5));
    }
}
