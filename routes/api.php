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

// Owner  Register and login
Route::post('owner/registerowner', [OwnerController::class, 'register']);
Route::post('owner/loginowner', [OwnerController::class, 'login']);

route::group(['middleware' => ['auth.user'], 'prefix' => 'owner'], function () {

    Route::post('updateowner', [OwnerController::class, 'updateowner']);
    Route::post('deleteowner', [OwnerController::class, 'deleteowner']);
    Route::post('logoutowner', [OwnerController::class, 'logout']);
});

// Routes for Shop
route::group(['middleware' => ['auth.user'], 'prefix' => 'owner'], function () {

    Route::post('storeshop',  [ShopController::class, 'storeshop']);
    Route::post('updateshop', [ShopController::class, 'updateshop'])->middleware('auth.owner.has.shop');
    Route::post('showshop',   [ShopController::class, 'showshop']);
    Route::Post('deleteshop', [ShopController::class, 'deleteshop'])->middleware('auth.owner.has.shop');
    Route::get('shopsrevenue', [InvoiceController::class, 'revenue']);
});
// Routes for Products
route::group(['middleware' => ['auth.user'], 'prefix' => 'product'], function () {

    Route::Post('addproduct', [ProductController::class, 'storeproduct'])->middleware('auth.owner.has.shop');
    Route::Post('updateproduct', [ProductController::class, 'updateproduct'])->middleware('auth.owner.has.shop');
    Route::get('showall', [ProductController::class, 'showAllProductOfEachShop']); //get all products for all shop for the user who has this shops
    Route::get('showProductsShopByName', [ProductController::class, 'showAllProductOfEachShopByName'])->middleware('auth.owner.has.shop'); //get all products for a specific shop
    Route::post('DeleteProduct', [ProductController::class, 'DeleteProduct'])->middleware('auth.owner.has.shop'); //get all products for a specific shop

});

// Routes For Clients
Route::post('client/login', [ClientController::class, 'login']);
Route::post('client/register', [ClientController::class, 'register']);

route::group(['middleware' => ['auth.client'],  'prefix' => 'client'], function () {

    Route::post('update', [ClientController::class, 'update']);
    Route::get('show', [ClientController::class, 'show']);
    Route::post('deleteaccount', [ClientController::class, 'delete']);
    Route::post('logout', [ClientController::class, 'logout']);
});

// Routes For Admins
Route::post('admin/login', [AdminController::class, 'login']);

route::group(['middleware' => ['auth.admin'],  'prefix' => 'admin'], function () {

    Route::post('register', [AdminController::class, 'register']); //only admin can add a new admin
    Route::post('showallshops', [ShopController::class, 'showallshops']); // show all shops data for admin website

});

// routes for client orders
route::group(['middleware' => ['auth.client'],  'prefix' => 'client'], function () {

    Route::post('makeorder', [OrderController::class, 'makeorder']);
    Route::post('deleteorder', [OrderController::class, 'deleteorder']);
    Route::get('Showallorders', [OrderController::class, 'Showallorders']);
});
