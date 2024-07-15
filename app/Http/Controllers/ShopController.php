<?php

namespace App\Http\Controllers;


use App\Models\Shop;
use Illuminate\Http\Request;
use App\Services\Shop\ShopService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Shop\CreateShopRequest;
use App\Http\Requests\Shop\DeleteShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;

class ShopController extends Controller
{
    protected $shopservice;
    public function __construct(ShopService $shopService)
    {
        $this->shopservice = $shopService;
    }
    public function  CheckUserHasThisShop(Request $request) // i make it middleware
    {
        $CheckUserHasThisShop = Shop::where('ownerId', Auth::id())->where('shopId', '=', $request->shopId)->first();
        return $CheckUserHasThisShop;
    }


    public  function storeshop(CreateShopRequest $request) // add shop data and connect to the auth user
    {
        $response = $this->shopservice->createShop($request);
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }

    public function updateshop(UpdateShopRequest $request) // update shop data using Auth::id after check that he has this shop by middleware
    {
        $response = $this->shopservice->updateShop($request);
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }

    public function deleteshop(DeleteShopRequest $request) // delete shop  using Auth::id after check that he has this shop by middleware and delete all products and the relationship between them
    {
        $response = $this->shopservice->delete($request);
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }
    public function restoreshop(DeleteShopRequest $request) // delete shop  using Auth::id after check that he has this shop by middleware and delete all products and the relationship between them
    {
        $response = $this->shopservice->restoreShop($request);
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }

    public  function showshops() // each user has a shops will show by this function
    {

        $response = $this->shopservice->showshop();
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }
    public function showallshops() // All Shops data For Admin
    {
        $response = $this->shopservice->showallshops();
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }
}
