<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\MunicipalityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::get('/states', [StateController::class, 'index']);
Route::get('/municipalities/{id}', [MunicipalityController::class, 'index']);