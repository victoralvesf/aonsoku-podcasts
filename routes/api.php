<?php

use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\PodcastController;
use Illuminate\Support\Facades\Route;

Route::prefix('podcasts')->group(function () {
    Route::get('/search', [PodcastController::class, 'search']);

    Route::get('/', [PodcastController::class, 'index']);
    Route::get('/{id}', [PodcastController::class, 'show']);
    Route::post('/', [PodcastController::class, 'store']);
    Route::delete('/{id}', [PodcastController::class, 'destroy']);

    Route::get('/{id}/episodes/search', [EpisodeController::class, 'search']);
    Route::get('/episodes/latest', [EpisodeController::class, 'latest']);
    Route::patch('/episodes/{id}/progress', [EpisodeController::class, 'progress']);
});
