<?php

use App\Http\Controllers\PodcastController;
use Illuminate\Support\Facades\Route;

Route::prefix('podcasts')->group(function () {
    Route::get('/', [PodcastController::class, 'index']);
    Route::get('/{id}', [PodcastController::class, 'show']);
    Route::post('/', [PodcastController::class, 'store']);
    Route::delete('/{id}', [PodcastController::class, 'destroy']);
});
