<?php

use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;

Route::get('proxy', ProxyController::class)->name('proxy');
