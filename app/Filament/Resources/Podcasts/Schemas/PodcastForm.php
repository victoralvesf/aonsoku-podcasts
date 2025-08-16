<?php

namespace App\Filament\Resources\Podcasts\Schemas;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Podcast Info')
                    ->icon(Heroicon::Microphone)
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('author')
                            ->required(),
                        Textarea::make('description')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('link')
                            ->label('Homepage URL')
                            ->default(''),
                        TextInput::make('feed_url')
                            ->label('Feed URL')
                            ->required(),
                    ]),
                Section::make('Image')
                    ->icon(Heroicon::Photo)
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        FileUpload::make('image_url')
                            ->hiddenLabel()
                            ->image()
                            ->disk('public')
                            ->directory('podcasts')
                            ->visibility('public')
                            ->mutateDehydratedStateUsing(function (?string $state): ?string {
                                if (blank($state)) {
                                    return null;
                                }

                                if (Str::startsWith($state, ['http://', 'https://'])) {
                                    return $state;
                                }

                                return Storage::disk('public')->url($state);
                            })
                            ->afterStateHydrated(static function (BaseFileUpload $component, string|array|null $state) {
                                $appBaseUrl = rtrim(Storage::disk('public')->url('/'), '/');

                                if (!blank($state) && Str::startsWith($state, needles: $appBaseUrl)) {
                                    $state = Str::after($state, "$appBaseUrl/");

                                    $component->state([((string) Str::uuid()) => $state]);
                                    return;
                                }

                                $component->state([]);
                            }),
                    ]),
                Section::make('Additional Info')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(3)
                    ->columnSpan(2)
                    ->schema([
                        Toggle::make('is_visible')
                            ->belowLabel('Make this podcast discoverable and accessible via the API.')
                            ->label('Public')
                            ->default(false)
                            ->columnSpan(2)
                            ->required(),
                        TextEntry::make('episode_count')
                            ->icon(Heroicon::Signal)
                            ->label('Episodes')
                            ->badge()
                            ->numeric()
                            ->hiddenOn('create'),
                    ]),
            ]);
    }
}
