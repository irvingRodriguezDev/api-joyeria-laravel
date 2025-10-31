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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\CashCutController;
use App\Http\Controllers\ReporteVentasController;
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
    Route::delete('/branches/{id}', [BranchController::class, 'delete']);
    //businessRule
    Route::post('/business-rules', [BusinessRuleController::class, 'store']);
    Route::get('/business-rules', [BusinessRuleController::class, 'index']);
    Route::delete('/business-rules/{id}', [BusinessRuleController::class, 'destroy']);
    //categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
    //lineas
    Route::post('/lines', [LineController::class, 'store']);
    Route::get('/lines',  [LineController::class, 'index']);
    Route::get('/lines/{id}',  [LineController::class, 'show']);
    Route::delete('/lines/{id}',  [LineController::class, 'destroy']);

    //products
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/productsNoPaginate', [ProductController::class, 'indexNoPaginate']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/productsByStatus/{id}', [ProductController::class, 'productsByStatus']);
    Route::get('/productsByStatus/{id}', [ProductController::class, 'productsByStatus']);
    Route::get('/productsSelect', [ProductController::class, 'productsForSelect']);
    Route::get('/productsAvailablePerBranch/{id}', [ProductController::class, 'ProductsAvailablePerBranch']);

    //clients
    Route::post('/clients', [CustomerController::class, 'store']);
    Route::get('/clients', [CustomerController::class, 'index']);
    Route::get('/all-clients', [CustomerController::class, 'indexPerBranch']);

    //sales
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/{id}', [SaleController::class, 'showSale']);
    Route::get('/sales/{id}/ticket', [SaleController::class, 'generateTicket']);
    Route::get('/ventas/hoy', [SaleController::class, 'totalVendidoHoy']);
    Route::get('/ventas/semana', [SaleController::class, 'totalVendidoSemana']);
    Route::get("/ventas/mes", [SaleController::class, 'totalVendidoMes']);

    //Dashboard
    Route::get('/total_gramos', [ProductController::class, 'totalGramos']);
    Route::get('/total_dinero_gramos', [ProductController::class, 'totalDineroGramos']);
    Route::get('/total_gramos_existentes', [ProductController::class, 'totalGramosExistentes']);
    Route::get('/total_dinero_gramos_existentes', [ProductController::class, 'TotalDineroGramosExistentes']);
    Route::get('/total_gramos_traspasados', [ProductController::class, 'totalGramosTraspasados']);
    Route::get('/total_dinero_gramos_traspasados',  [ProductController::class, 'totalDineroGramosTraspasados']);
    Route::get('/total_gramos_danados', [ProductController::class, 'totalGramosDanados']);
    Route::get('/total_dinero_gramos_danados', [ProductController::class, 'totalDineroGramosDanados']);
    Route::get('/total_piezas', [ProductController::class, 'totalPiezas']);
    Route::get('/total_dinero_piezas', [ProductController::class, 'totalDineroPiezas']);
    Route::get('/total_piezas_existentes', [ProductController::class, 'totalPiezasExistentes']);
    Route::get('/total_dinero_piezas_existentes', [ProductController::class, 'TotalDineroPiezasExistentes']);
    Route::get('/total_piezas_traspasados', [ProductController::class, 'totalPiezasTraspasados']);
    Route::get('/total_dinero_piezas_traspasados',  [ProductController::class, 'totalDineroPiezasTraspasados']);
    Route::get('/total_piezas_danados', [ProductController::class, 'totalPiezasDanados']);
    Route::get('/total_dinero_piezas_danados', [ProductController::class, 'totalDineroPiezasDanados']);

    //departure
    Route::get('/departures', [DepartureController::class, 'index']);
    Route::post('/departures', [DepartureController::class, 'store']);
    Route::get('/departures/{id}', [DepartureController::class, 'show']);
    Route::delete('/departures/{id}', [DepartureController::class, 'destroy']);
    Route::get('/departures/{id}/pdf', [DepartureController::class, 'generatePDF']);

    //typesProducts
    Route::get('/typeProducts', [TypeProductController::class, 'index']); 
    //users
    Route::get('/users', [UserController::class, 'index']); 

    //reports
    Route::post('/reports/inventory', [InventoryReportController::class, 'generatePdf']);

    //boxcut
    Route::post('/reports/cashcut/range', [CashCutController::class, 'rangePdf']);
    Route::post('/reports/cashcut/daily-ticket', [CashCutController::class, 'dailyTicket']);

    //reporte de ventas
    Route::post('/reports/sales-range', [ReporteVentasController::class, 'ventasPorRango']);



});
Route::get('/states', [StateController::class, 'index']);
Route::get('/municipalities/{id}', [MunicipalityController::class, 'index']);
Route::get('/typeUsers', [TypeUserController::class, 'index']);