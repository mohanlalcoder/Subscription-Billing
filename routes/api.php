<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsageController;

Route::middleware('throttle:usage')->group(function () {
    Route::post('/usage', [UsageController::class, 'store']);
});
