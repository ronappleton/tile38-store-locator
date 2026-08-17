<?php

use App\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowcaseController::class)->name('home');
