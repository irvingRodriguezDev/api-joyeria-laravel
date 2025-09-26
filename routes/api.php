<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\BranchController; // Asegúrate de importar BranchController
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (Públicas)
|--------------------------------------------------------------------------
| Estas rutas no requieren que el usuario esté autenticado.
*/

Route::prefix('auth')->group(function () {
    // Registro de nuevo usuario (y tienda, si aplica)
    Route::post('/register', [AuthController::class, 'register']);
    
    // Inicio de sesión
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren 'auth:sanctum')
|--------------------------------------------------------------------------
| Todas estas rutas son accesibles solo si se proporciona un token Sanctum válido.
*/

Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Tiendas (Shops)
    Route::get('/shops', [ShopController::class, 'index']);

    // Sucursales (Branches)
    // Se movió aquí y se quitó el prefijo 'auth'
    Route::post('/branches', [BranchController::class, 'store']); 

});
Route::get('/states', [StateController::class, 'index']);
Route::get('/municipalities/{id}', [MunicipalityController::class, 'index']);