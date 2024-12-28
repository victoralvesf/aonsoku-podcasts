<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function (Request $request) {
    return response()->json([
        'message' => 'Hello World',
        'user' => $request->user,
        'tenant' => $request->tenant,
    ]);
});
