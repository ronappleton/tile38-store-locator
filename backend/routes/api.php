<?php

use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/stores', [StoreController::class, 'index']);
Route::post('/stores/viewport', [StoreController::class, 'viewport']);
Route::get('/stores/benchmark', [StoreController::class, 'benchmark']);
