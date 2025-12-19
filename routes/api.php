<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CapitalController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DebtController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\MaterialCategoryController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductionController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\WageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/inventory-status', [DashboardController::class, 'inventoryStatus']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/purchases', [ReportController::class, 'purchases']);
    Route::get('/reports/productions', [ReportController::class, 'productions']);
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);

    // Master Data
    Route::apiResource('material-categories', MaterialCategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('materials', MaterialController::class);

    // Transactions
    Route::apiResource('recipes', RecipeController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('productions', ProductionController::class);
    Route::apiResource('orders', OrderController::class);

    // Finance
    Route::apiResource('capitals', CapitalController::class);
    Route::apiResource('debts', DebtController::class);
    Route::post('/debts/{debt}/payments', [DebtController::class, 'addPayment']);
    Route::apiResource('wages', WageController::class);

    // Assets
    Route::apiResource('assets', AssetController::class);
    Route::post('/assets/{asset}/depreciate', [AssetController::class, 'depreciate']);
});
