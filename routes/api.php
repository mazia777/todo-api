<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
