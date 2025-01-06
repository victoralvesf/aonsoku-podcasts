<?php

use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\PodcastController;
use Illuminate\Support\Facades\Route;

Route::prefix('podcasts')->group(function () {
    Route::get('/search', [PodcastController::class, 'search'])->name('podcasts.search');

    Route::get('/', [PodcastController::class, 'index'])->name('podcasts.index');
    Route::get('/{id}', [PodcastController::class, 'show'])->name('podcasts.show');
    Route::post('/', [PodcastController::class, 'store'])->name('podcasts.store');
    Route::delete('/{id}', [PodcastController::class, 'destroy'])->name('podcasts.destroy');

    Route::get('/{id}/episodes/search', [EpisodeController::class, 'search'])->name('episodes.search');
    Route::get('/episodes/latest', [EpisodeController::class, 'latest'])->name('episodes.latest');
    Route::patch('/episodes/{id}/progress', [EpisodeController::class, 'progress'])->name('episodes.progress');
});
