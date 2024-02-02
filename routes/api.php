<?php

use App\Http\Controllers\Api\AccommodationApiController;
use App\Http\Controllers\Api\ContractApiController;
use Illuminate\Support\Facades\Route;

Route::apiResource('accommodations', AccommodationApiController::class);
Route::apiResource('contracts', ContractApiController::class);
