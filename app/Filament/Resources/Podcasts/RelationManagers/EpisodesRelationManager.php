<?php

namespace App\Filament\Resources\Podcasts\RelationManagers;

use App\Form\ImageUpload;
use App\Form\TextEditor;
use App\Models\Episode;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EpisodesRelationManager extends RelationManager
{
    protected static string $relationship = 'episodes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Episode Title')
                                    ->required(),
                                TextInput::make('audio_url')
                                    ->label('Audio URL')
                                    ->required(),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('duration')
                                            ->required()
                                            ->numeric(),
                                        DateTimePicker::make('published_at')
                                            ->label('Published At')
                                            ->columnSpan(2)
                                            ->required(),
                                    ])
                            ]),
                        ImageUpload::make('image_url')
                            ->label('Image')
                            ->directory('episodes'),
                    ]),
                TextEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultKeySort(false)
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Image'),
                TextColumn::make('title')
                    ->label('Episode Title')
                    ->wrap()
                    ->limit(110)
                    ->searchable(),
                TextColumn::make('audio_url')
                    ->label('Audio URL')
                    ->copyable(true)
                    ->copyableState(fn(Episode $record) => $record->audio_url)
                    ->icon(Heroicon::ClipboardDocument)
                    ->limit(35),
                TextColumn::make('duration')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function (int $state) {
                        if (!is_numeric($state) || $state < 0) {
                            return $state;
                        }

                        $seconds = $state;

                        if ($seconds < 3600) {
                            $minutes = floor($seconds / 60);
                            return sprintf('%dm', $minutes);
                        }

                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);

                        if ($minutes == 0) {
                            return sprintf('%dh', $hours);
                        }

                        return sprintf('%dh %02dm', $hours, $minutes);
                    }),
                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                ])
            ])
            ->toolbarActions([]);
    }
}
