<?php




use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\ShopController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use  App\Http\Controllers\OwnerController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes for Owner  Register and login
Route::POST('owner/registerowner', [OwnerController::class, 'register']);
Route::POST('owner/loginowner', [OwnerController::class, 'login']);
Route::POST('owner/restore', [OwnerController::class, 'restore']);

Route::group(['middleware' => ['auth.user'], 'prefix' => 'owner'], function () {

    Route::POST('updateowner', [OwnerController::class, 'updateowner']);
    Route::POST('deleteowner', [OwnerController::class, 'deleteowner']);
    Route::POST('logoutowner', [OwnerController::class, 'logout']);
});

// Routes for Shop
Route::group(['middleware' => ['auth.user'], 'prefix' => 'owner'], function () {

    Route::POST('storeshop',   [ShopController::class, 'storeshop']);
    Route::POST('updateshop',  [ShopController::class, 'updateshop'])->middleware('auth.owner.has.shopById');
    Route::POST('showshops',    [ShopController::class, 'showshops']);
    Route::POST('deleteshop',  [ShopController::class, 'deleteshop'])->middleware('auth.owner.has.shopById');
    Route::POST('restoreShop',  [ShopController::class, 'restoreShop'])->middleware('auth.owner.has.shopById');
    Route::get('shopsrevenue', [InvoiceController::class, 'revenue']);
});

// Routes for Products
Route::group(['middleware' => ['auth.user'], 'prefix' => 'product'], function () {

    Route::Post('addproduct', [ProductController::class, 'storeproduct'])->middleware('auth.owner.has.shopById');
    Route::Post('updateproduct', [ProductController::class, 'updateproduct'])->middleware('auth.owner.has.shopById');
    Route::get('showShopWithProducts', [ProductController::class, 'showAllProductOfEachShop']);
    Route::get('showShopProductsById', [ProductController::class, 'showAllProductOfShopById'])->middleware('auth.owner.has.shopById');
    Route::post('DeleteProduct', [ProductController::class, 'DeleteProduct'])->middleware('auth.owner.has.shopById');
});

// Routes For Clients
Route::post('client/login', [ClientController::class, 'login']);
Route::post('client/register', [ClientController::class, 'register']);
Route::post('client/restore', [ClientController::class, 'restore']);

Route::group(['middleware' => ['auth.client'],  'prefix' => 'client'], function () {
    Route::post('update', [ClientController::class, 'update']);
    Route::get('show', [ClientController::class, 'show']);
    Route::post('deleteaccount', [ClientController::class, 'delete']);
    Route::post('logout', [ClientController::class, 'logout']);
});

// Routes For Admins
Route::post('admin/login', [AdminController::class, 'login']);

Route::group(['middleware' => ['auth.admin'],  'prefix' => 'admin'], function () {
    Route::post('register', [AdminController::class, 'register']); //only admin can add a new admin
    Route::post('logout', [AdminController::class, 'logout']);
    Route::get('showallshops', [AdminController::class, 'showallshops']); // show all shops data for admin website
});

// routes for client orders
Route::group(['middleware' => ['auth.client'],  'prefix' => 'client'], function () {

    Route::post('makeorder', [OrderController::class, 'makeorder']);
    Route::post('deleteorder', [OrderController::class, 'deleteorder']);
    Route::get('Showallorders', [OrderController::class, 'Showallorders']);
});
