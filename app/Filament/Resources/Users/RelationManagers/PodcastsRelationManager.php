<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Models\Podcast;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PodcastsRelationManager extends RelationManager
{
    protected static string $relationship = 'podcasts';

    protected static ?string $title = 'Followed Podcasts';

    protected static ?string $relatedResource = PodcastResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AttachAction::make()
                    ->label('Follow Podcast')
                    ->modalHeading('Follow a new Podcast')
                    ->modalSubmitActionLabel('Follow')
                    ->attachAnother(false)
                    ->preloadRecordSelect()
                    ->multiple(),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Unfollow')
                    ->modalHeading(fn (Podcast $record) => 'Unfollow ' . $record->title . '?')
                    ->modalSubmitActionLabel('Unfollow'),
            ]);
    }
}
