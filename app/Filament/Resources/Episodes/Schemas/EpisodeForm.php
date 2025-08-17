<?php

namespace App\Filament\Resources\Episodes\Schemas;

use App\Form\ImageUpload;
use App\Form\TextEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Episode Info')
                            ->icon(Heroicon::InformationCircle)
                            ->columns(1)
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Episode Title')
                                    ->required(),
                                TextInput::make('audio_url')
                                    ->label('Audio URL')
                                    ->required(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('duration')
                                            ->required()
                                            ->numeric(),
                                        DateTimePicker::make('published_at')
                                            ->label('Published At')
                                            ->required(),
                                    ])
                            ]),
                        Section::make('Image')
                            ->icon(Heroicon::Photo)
                            ->schema([
                                ImageUpload::make('image_url')
                                    ->hiddenLabel()
                                    ->directory('episodes'),
                            ])
                    ]),
                Section::make('Description')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        TextEditor::make('description')
                            ->hiddenLabel()
                            ->required()
                            ->columnSpanFull()
                    ])
            ]);
    }
}
