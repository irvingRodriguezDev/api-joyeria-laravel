<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TypeUserController;
use App\Http\Controllers\BranchController; // Asegúrate de importar BranchController
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TypeProductController;
use App\Http\Controllers\BusinessRuleController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\ProductController;
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
    Route::middleware('auth:sanctum')->post('/users', [UserController::class, 'store']);

    // Tiendas (Shops)
    Route::get('/shops', [ShopController::class, 'index']);

    // Sucursales (Branches)
    // Se movió aquí y se quitó el prefijo 'auth'
    Route::post('/branches', [BranchController::class, 'store']); 
    Route::get('/branches', [BranchController::class, 'index']); 
    //businessRule
    Route::post('/business-rules', [BusinessRuleController::class, 'store']);
    Route::get('/business-rules', [BusinessRuleController::class, 'index']);
    //categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    //lineas
    Route::post('/lines', [LineController::class, 'store']);
    Route::get('/lines',  [LineController::class, 'index']);
    Route::get('/lines/{id}',  [LineController::class, 'show']);

    //products
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);

    //typesProducts
    Route::get('/typeProducts', [TypeProductController::class, 'index']); 
    //users
    Route::get('/users', [UserController::class, 'index']); 

    


});
Route::get('/states', [StateController::class, 'index']);
Route::get('/municipalities/{id}', [MunicipalityController::class, 'index']);
Route::get('/typeUsers', [TypeUserController::class, 'index']);