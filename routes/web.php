<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('merchants.index');
});

Route::resource('merchants', MerchantController::class);

Route::resource('plans', PlanController::class)
    ->except(['show']);

Route::resource('customers', CustomerController::class)
    ->except(['show']);

Route::resource('subscriptions', SubscriptionController::class)
    ->only(['index', 'create', 'store']);

Route::get(
    '/merchants/{merchant}/dashboard',
    [DashboardController::class, 'index']
)->name('merchants.dashboard');

Route::get('/usage', [UsageController::class, 'index'])
    ->name('usage.index');

Route::get('/invoices', [InvoiceController::class, 'index'])
    ->name('invoices.index');

Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
    ->name('invoices.show');
