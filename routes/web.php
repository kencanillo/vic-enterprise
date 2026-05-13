<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('/sales-orders/{salesOrder}/print-log', [SalesOrderController::class, 'printLog'])->name('sales-orders.print-log');

    Route::resource('dispatches', DispatchController::class)->except(['destroy']);
    Route::post('/dispatches/{dispatch}/mark-in-transit', [DispatchController::class, 'markInTransit'])->name('dispatches.mark-in-transit');
    Route::post('/dispatches/{dispatch}/mark-delivered', [DispatchController::class, 'markDelivered'])->name('dispatches.mark-delivered');
    Route::post('/dispatches/{dispatch}/cancel', [DispatchController::class, 'cancel'])->name('dispatches.cancel');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/update', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/update', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');

    foreach (['customers','products','warehouses','haulers','vehicles','drivers','operational-areas','payment-terms','sales-agents'] as $resource) {
        Route::get("/$resource", [MasterDataController::class, 'index'])->name("$resource.index")->defaults('resource', $resource);
        Route::post("/$resource", [MasterDataController::class, 'store'])->name("$resource.store")->defaults('resource', $resource);
        Route::put("/$resource/{id}", [MasterDataController::class, 'update'])->name("$resource.update")->defaults('resource', $resource);
        Route::delete("/$resource/{id}", [MasterDataController::class, 'destroy'])->name("$resource.destroy")->defaults('resource', $resource);
    }

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales-orders', [ReportController::class, 'salesOrders'])->name('reports.sales-orders');
    Route::get('/reports/dispatches', [ReportController::class, 'dispatches'])->name('reports.dispatches');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});