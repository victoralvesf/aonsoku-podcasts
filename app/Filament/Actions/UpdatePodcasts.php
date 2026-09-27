<?php

namespace App\Filament\Actions;

use App\Jobs\TriggerPodcastsUpdate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class UpdatePodcasts extends Action
{
    protected static string $throttleKey = 'podcasts-update-throttle';

    private const string TOOLTIP_MESSAGE = 'Wait a few minutes before trying to update again.';

    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Update Podcasts')
            ->button()
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->disabled(fn () => Cache::has(static::$throttleKey))
            ->tooltip(fn () => Cache::get(static::$throttleKey) ? self::TOOLTIP_MESSAGE : null)
            ->action(fn () => self::updatePodcastsAction());
    }

    public static function getDefaultName(): ?string
    {
        return 'update-podcasts';
    }

    protected static function updatePodcastsAction(): void
    {
        $canAddThrottle = Cache::add(static::$throttleKey, true, now()->addMinutes(10));

        if (!$canAddThrottle) {
            Notification::make()
                ->title('Update already in progress')
                ->body('Wait for the waiting period to end.')
                ->warning()
                ->send();

            return;
        }

        TriggerPodcastsUpdate::dispatch();

        Notification::make()
            ->title('Update started')
            ->body("All podcasts will be updated shortly.")
            ->success()
            ->send();
    }
}
