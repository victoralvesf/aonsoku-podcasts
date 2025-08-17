<?php

namespace App\Filament\Resources\Podcasts\Tables;

use App\Models\Podcast;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PodcastsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Image'),
                TextColumn::make('title')
                    ->label('Podcast')
                    ->searchable()
                    ->description(fn(Podcast $record): string => $record->author),
                TextColumn::make('author')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('link')
                    ->copyable()
                    ->copyableState(fn(Podcast $record) => $record->link)
                    ->icon(Heroicon::ClipboardDocument)
                    ->limit(35)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('feed_url')
                    ->label('Feed URL')
                    ->copyable()
                    ->copyableState(fn(Podcast $record) => $record->feed_url)
                    ->icon(Heroicon::ClipboardDocument)
                    ->limit(35),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->trueColor('primary')
                    ->boolean(),
                TextColumn::make('episode_count')
                    ->label('Episodes')
                    ->icon(Heroicon::Signal)
                    ->badge()
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
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                ])
            ])
            ->toolbarActions([]);
    }
}
