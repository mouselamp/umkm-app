<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/', function () {
    return view('dashboard');
});

// Master Data
Route::get('/products', function () {
    return view('products.index');
});
Route::get('/materials', function () {
    return view('materials.index');
});
Route::get('/customers', function () {
    return view('customers.index');
});
Route::get('/suppliers', function () {
    return view('suppliers.index');
});
Route::get('/employees', function () {
    return view('employees.index');
});
Route::get('/recipes', function () {
    return view('recipes.index');
});

// Transactions
Route::get('/purchases', function () {
    return view('purchases.index');
});
Route::get('/productions', function () {
    return view('productions.index');
});
Route::get('/orders', function () {
    return view('orders.index');
});

// Finance
Route::get('/finance/capitals', function () {
    return view('finance.capitals');
});
Route::get('/finance/debts', function () {
    return view('finance.debts');
});
Route::get('/finance/wages', function () {
    return view('finance.wages');
});

// Assets
Route::get('/assets', function () {
    return view('assets.index');
});

// Reports
Route::get('/reports', function () {
    return view('reports.index');
});
