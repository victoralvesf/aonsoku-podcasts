<?php

namespace App\Filament\Resources\Podcasts\Schemas;

use App\Form\ImageUpload;
use App\Form\TextEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                        TextInput::make('link')
                            ->label('Homepage URL')
                            ->default(''),
                        TextInput::make('feed_url')
                            ->label('Feed URL')
                            ->required(),
                        TextEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Image')
                    ->icon(Heroicon::Photo)
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        ImageUpload::make('image_url')
                            ->hiddenLabel()
                            ->directory('podcasts'),
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
                            ->hiddenOn('create'),
                    ]),
            ]);
    }
}
