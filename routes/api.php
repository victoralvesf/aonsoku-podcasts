<?php

use App\Http\Controllers\PodcastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('podcast')->group(function () {
    Route::get('/', [PodcastController::class, 'index']);
    Route::post('/', [PodcastController::class, 'store']);
    Route::delete('/{id}', [PodcastController::class, 'destroy']);
});

Route::get('/hello', function (Request $request) {
    return response()->json([
        'message' => 'Hello World',
        'user' => $request->user,
        'tenant' => $request->tenant,
    ]);
});
