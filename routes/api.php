<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->group(function () {

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::get('/admin/logout', [AdminAuthController::class, 'logout']);

        Route::get('/admin', function (Request $request) {
            return $request->user();
        });

        // Admin Role Group

        Route::get('/admin/roles', [RoleController::class, 'index']);

        // Admin Dashboard Start

        Route::get('admin/dashboard/overallStaticData', [DashboardController::class, 'overallStaticData']);

        // Admin Dashboard End

        // Admin SupplierManagment Start

        // Admin Supplier Group
        Route::get('/admin/suppliers', [SupplierController::class, 'index']);

        Route::post('/admin/suppliers/create', [SupplierController::class, 'store']);

        Route::post('/admin/suppliers/edit', [SupplierController::class, 'update']);

        Route::post('/admin/suppliers/{id}/delete', [SupplierController::class, 'delete']);

        // Admin Purchase Group
        Route::get('/admin/purchases', [PurchaseController::class, 'index']);

        Route::post('/admin/purchases/create', [PurchaseController::class, 'store']);

        Route::post('/admin/purchases/edit', [PurchaseController::class, 'update']);

        Route::post('/admin/purchases/{id}/confirm', [PurchaseController::class, 'confirm']);

        Route::post('/admin/purchases/{id}/cancel', [PurchaseController::class, 'cancel']);

        Route::get('/admin/purchases/itemListbyCategory', [PurchaseController::class, 'itemListbyCategory']);

        Route::post('/admin/purchases/requestPurchase', [PurchaseController::class, 'requestPurchase']);

        // Admin SupplierManagment End

        // Admin InventoryManagement Start

        // Admin Inventory Group
        Route::get('/admin/inventories', [InventoryItemController::class, 'index']);

        Route::post('/admin/inventories/create', [InventoryItemController::class, 'store']);

        Route::post('/admin/inventories/edit', [InventoryItemController::class, 'update']);

        Route::post('/admin/inventories/{id}/delete', [InventoryItemController::class, 'delete']);

        // Admin Item Category Group
        Route::get('/admin/item-categories', [ItemCategoryController::class, 'index']);

        Route::post('/admin/item-categories/create', [ItemCategoryController::class, 'store']);

        Route::post('/admin/item-categories/edit', [ItemCategoryController::class, 'update']);

        Route::post('/admin/item-categories/{id}/delete', [ItemCategoryController::class, 'delete']);

        Route::post('/admin/inventories/{id}/changeStatus', [InventoryItemController::class, 'changeStatus']);

        // Admin InventoryManagement End

        // Admin MenuManagement Start

        // Admin Category Group
        Route::get('/admin/menu-categories', [MenuCategoryController::class, 'index']);

        Route::post('/admin/menu-categories/create', [MenuCategoryController::class, 'store']);

        Route::post('/admin/menu-categories/edit', [MenuCategoryController::class, 'update']);

        Route::post('/admin/menu-categories/{id}/delete', [MenuCategoryController::class, 'delete']);

        // Admin Menu Group
        Route::get('/admin/menus', [MenuController::class, 'index']);

        Route::post('/admin/menus/create', [MenuController::class, 'store']);

        Route::post('/admin/menus/edit', [MenuController::class, 'update']);

        Route::post('/admin/menus/{id}/delete', [MenuController::class, 'delete']);

        Route::post('/admin/menus/addonItem', [MenuController::class, 'addonItem']);

        // Admin MenuManagement End

        // Admin EmployeeManagement Start

        Route::get('/admin/employees', [EmployeeController::class, 'index']);

        Route::post('/admin/employees/create', [EmployeeController::class, 'store']);

        Route::post('/admin/employees/edit', [EmployeeController::class, 'update']);

        Route::post('/admin/employees/{id}/delete', [EmployeeController::class, 'delete']);

        // Admin TableManagement Start

        Route::get('/admin/tables', [TableController::class, 'index']);

        Route::post('/admin/tables/create', [TableController::class, 'store']);

        Route::post('/admin/tables/edit', [TableController::class, 'update']);

        Route::post('/admin/tables/{id}/delete', [TableController::class, 'delete']);

        // Admin TableManagement End

        // Admin OrderManagement Start

        Route::get('/admin/orders', [OrderController::class, 'index']);
        

        // Admin OrderManagement End

         // Admin PaymentManagement Start

         Route::get('/admin/payments', [PaymentController::class, 'index']);
        

         // Admin PaymentManagement End
    });

    Route::middleware(['auth:sanctum', 'user'])->group(function () {

        // Waiter Route Start

        Route::get('/waiter/orders/readyOrderList', [OrderController::class, 'readyOrderList']);

        Route::get('/waiter/tableList', [TableController::class, 'tableList']);

        Route::get('/waiter/currentTableList', [TableController::class, 'currentTableList']);

        Route::get('/waiter/menus', [MenuController::class, 'index']);

        Route::get('/waiter/menu-categories', [MenuCategoryController::class, 'index']);

        Route::post('/waiter/orders/proceedOrder', [OrderController::class, 'proceedOrder']);

        Route::get('/waiter/orders/getOrderById', [OrderController::class, 'getOrderById']);

        Route::post('/waiter/orders/serveOrder', [OrderController::class, 'serveOrder']);

        Route::post('/waiter/orders/requestBill', [PaymentController::class, 'requestBill']);

        // Waiter Route End

        // Chef Route Start

        Route::get('/chef/currentOrderList', [OrderController::class, 'currentOrderList']);

        Route::post('/chef/startPreparing', [OrderController::class, 'startPreparing']);

        Route::post('/chef/markAsReady', [OrderController::class, 'markAsReady']);

        // Cashier Route Start

        Route::get('/cashier/paymentOrder', [PaymentController::class, 'paymentOrder']);

        Route::post('/cashier/processPayment', [PaymentController::class, 'processPayment']);








    });

Route::post('/authorization/login', [EmployeeAuthController::class, 'login']);

Route::post('/authorization/adminlogin', [AdminAuthController::class, 'login']);
