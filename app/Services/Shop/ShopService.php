<?php

namespace App\Services\Shop;

use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Services\Product\ProductService;
use App\Http\Requests\Shop\CreateShopRequest;
use App\Http\Requests\Shop\DeleteShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;

class ShopService
{
    protected $productservice;
    public function __construct(ProductService  $productservice)
    {
        $this->productservice = $productservice;
    }
    public function createShop(CreateShopRequest $request)
    {
        try {
            Shop::create(
                [

                    'nameOfStore' => $request->nameOfStore,
                    'storeLocation' => $request->storeLocation,
                    'ownerId' => Auth::id(),

                ]
            );
            return [
                "message" => "Shop Created successfully!",
                "status" => 200
            ];
        } catch (\Exception $ex) {
            return ["message" =>  'error: ' . $ex->getMessage(), "status" => $ex->getCode()];
        }
    }

    public function updateShop(UpdateShopRequest $request)
    {
        try {

            $shopdata = Shop::where('shopId', '=', $request->shopId)->first();

            $shopdata->update(
                [
                    'nameOfStore' => $request->nameOfStore,
                    'storeLocation' => $request->storeLocation,

                ]
            );
            if ($shopdata) {
                $this->updateShopIDProduct($shopdata);
            }
            //    /

            return ["message" =>  "data updated", "status" => 200];
        } catch (\Exception $ex) {
            return ["message" =>    $ex->getMessage(), "status" => $ex->getCode()];
        }
    }
    public function updateShopIDProduct($shopdata)
    {
        $shopProducts = Product::where('shopId', '=', $shopdata->shopId)->get();  //return all recordes that has the same name
        foreach ($shopProducts as $product) {
            $product->update([
                'shopId' => $shopdata->shopId
            ]);
        }
    }
    public function delete(DeleteShopRequest $request)
    {
        try {

            $shop = Shop::find($request->shopId);
            if ($shop) {  //handel this in meddelware?
                $numberofProducts = $this->productservice->getCountOfProductsForSpecificShop($shop);
                $Productsdelete = $this->productservice->deleteProductsForSpecificShop($shop);
                if ($Productsdelete || $numberofProducts == 0) {
                    $shop->delete();
                    return ["message" =>  "Shop Deleted", "status" => 200];
                }
            }
        } catch (\Exception $ex) {

            return ["message" => $ex->getMessage(), "status" => $ex->getCode()];
        }
    }

    public function deleteShop($shop) //use this method in ownerService when owner delete his account
    {
        $checkdelete = $shop->delete();
        if (!$checkdelete) {
            return [
                'message' => 'This Shop does not deleted',
                "status" => 404
            ];
        }
    }
    public function restoreShops($userID) //use this method in ownerService when owner restore his account
    {
        Shop::withTrashed()->where('ownerId', $userID)->restore();
        $shops = Shop::where('ownerId', $userID)->get();
        return $shops;
    }
    public function restoreShop(DeleteShopRequest $request)   // !refactor
    {
        Shop::withTrashed()->where('shopId', $request->shopId)->restore();

        $shops = Shop::where('shopId', $request->shopId)->get();

        $this->productservice->restoreProducts($shops);
        return [
            'message' => 'This Shop restored successfully',
            "status" => 200
        ];
    }
    public function showallshops() // All Shops data For Admin
    {
        try {

            $shops = Shop::all();

            foreach ($shops as $shop) {
                $ownerId = $shop['ownerId'];
                $shopId = $shop['shopId'];
                $productcount = Product::where('shopId', $shopId)->count();
                $shop['Number Of Products'] = $productcount;
                $shop['owner Data'] = User::where('id',  $ownerId)->get(['nameOfOwner as Owner Name', 'email', 'PhoneNumberOfOwner as Phone Number']);
            }
            if ($shops == '[]') {
                return ['message' => 'No Shops Added yet', "status" => 200];
            }
            return ['message' => $shops, "status" => 200];
        } catch (\Exception $ex) {
            return ["message" => $ex->getMessage(), "status" => $ex->getCode()];
        }
    }
    public  function showshop() //auth user can show all shops using this method
    {
        try {
            $data = User::with('shops')->find(auth::id());
            if ($data->shops->isEmpty()) {
                return ['message' => 'You do not have any shops', "status" => 200];
            }
            return ['message' => $data, "status" => 200];
        } catch (\Exception $ex) {
            return ["message" => $ex->getMessage(), "status" => $ex->getCode()];
        }
    }
    public function getShopsForAuth()
    {
        return Shop::where('ownerId', Auth::id())->get();
    }
}
