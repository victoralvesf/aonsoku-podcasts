<?php

namespace App\Filament\Actions;

use App\Jobs\ProcessPodcast;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ImportPodcast extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Import Podcast')
            ->button()
            ->icon(Heroicon::CloudArrowDown)
            ->color('gray')
            ->schema([
                TextInput::make('url')
                    ->label('Feed URL')
                    ->required(),
            ])
            ->action(function (array $data) {
                $feedUrl = $data['url'];

                self::importPodcastAction($feedUrl);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'import-podcast';
    }

    protected static function importPodcastAction(string $feedUrl): void
    {
        ProcessPodcast::dispatch(null, $feedUrl);

        Notification::make()
            ->title('Podcast import started')
            ->body("The podcast will be available shortly.")
            ->success()
            ->send();
    }
}
