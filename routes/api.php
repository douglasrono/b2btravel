<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContractApiController;
use App\Http\Controllers\Api\AccommodationApiController;



Route::middleware('auth:api')->group(function () {
    Route::apiResource('accommodations', AccommodationApiController::class);
    Route::apiResource('contracts', ContractApiController::class);
});


