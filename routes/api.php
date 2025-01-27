<?php

use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\PodcastController;
use Illuminate\Support\Facades\Route;

Route::get('/podcasts/search', [PodcastController::class, 'search'])->name('podcasts.search');

Route::resource('podcasts', PodcastController::class)
    ->only(['index', 'store', 'show', 'destroy']);

Route::prefix('episodes')->name('episodes.')->group(function () {
    Route::get('/podcast/{id}/search', [EpisodeController::class, 'search'])->name('search');
    Route::get('/latest', [EpisodeController::class, 'latest'])->name('latest');
    Route::patch('/{id}/progress', [EpisodeController::class, 'progress'])->name('progress');
    Route::get('/{id}', [EpisodeController::class, 'show'])->name('show');
});
