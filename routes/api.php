<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslationController;

Route::middleware('api')->group(function () {
    Route::post('/translation/detect', [TranslationController::class, 'detect']);
    Route::post('/translation/translate', [TranslationController::class, 'translate']);
});
