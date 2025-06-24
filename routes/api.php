<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HaberlerController;


Route::prefix('v1')->group(function () {
    Route::get('haberler', [HaberlerController::class, 'index']);
    Route::get('haberler/{id}', [HaberlerController::class, 'show']);

    // Protected routes
    //Route::middleware('auth.token')->group(function () {
        Route::post('haberler', [HaberlerController::class, 'store']);
        Route::put('haberler/{id}', [HaberlerController::class, 'update']);
        Route::delete('haberler/{id}', [HaberlerController::class, 'destroy']);
    //});
});