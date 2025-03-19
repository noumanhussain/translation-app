<?php

use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public authentication routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Protected API routes
Route::group(['middleware' => 'auth:api'], function () {
    // Languages
    Route::apiResource('languages', LanguageController::class);

    // Translations
    Route::apiResource('translations', TranslationController::class);
});
