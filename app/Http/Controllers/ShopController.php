<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;




class ShopController extends Controller
{

    public function  CheckUserHasThisShop(Request $request) // i make it middleware
    {
        $CheckUserHasThisShop = shop::where('ownerId', Auth::id())->where('shopId', '=', $request->shopId)->first();
        return $CheckUserHasThisShop;
    }
    public function validate_ID_request(Request $request)  //validate shopid
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shopId' => 'required|exists:shop,shopId',
            ]

        );
        return $validator;
    }
    public function validate_all_request(Request $request)  //validate shopid name location
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shopId' => 'required|exists:shop,shopId',
                'nameOfStore' => 'required|string|unique:shop',
                'storeLocation' => 'required|string',
            ]

        );
        return $validator;
    }
    public function validaterequest(Request $request)  // name location
    {
        $validator = Validator::make(
            $request->all(),
            [

                'nameOfStore' => 'required|string|unique:shop',
                'storeLocation' => 'required|string',
            ]

        );
        return $validator;
    }
    public  function storeshop(Request $request) // add shop data and cnnect to the auth user
    {
        try {

            if (!$this->validaterequest($request)->fails()) {
                $store = Shop::create(
                    [

                        'nameOfStore' => $request->nameOfStore,
                        'storeLocation' => $request->storeLocation,
                        'ownerId' => Auth::id(),

                    ]
                );
                if ($store) {

                    return response()->json($store, 200);
                } else {
                    return response()->json('No Data added', 500);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' =>  $this->validaterequest($request)->errors()
                ]);
            }
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()], 500);
        }
    }


    public function updateshop(Request $request) // update shop data using Auth::id after check that he has this shop by middleware
    {

        if ($this->validate_ID_request($request)->fails()) {
            return response()->json([
                'error' => true,
                'message' =>  $this->validate_ID_request($request)->errors()
            ]);
        }

        //    $shopdata->nameOfStore = '';
        //     $shopdata->save();

        // User::with('shops')->get()->where('id',  auth::id());


        if (!$this->validate_all_request($request)->fails()) {
            $shopdata = Shop::where('shopId', '=', $request->shopId)->first();
            $copyshopdata = Shop::where('shopId', '=', $request->shopId)->first();
        } else {
            return response()->json([
                'error' => true,
                'message' =>  $this->validate_all_request($request)->errors()
            ]);
        }

        // go to shop elequant add  protected $primaryKey = 'shopId'; because primary key name is not id

        $shopdata->update(
            [
                'nameOfStore' => $request->nameOfStore,
                'storeLocation' => $request->storeLocation,

            ]
        );
        if ($shopdata) {
            $shopProducts = Product::where('shopname', '=', $copyshopdata->nameOfStore)->get();  //return all recordes that has the same name
            // return response()->json( $shopProducts );
            foreach ($shopProducts as $product) {
                $product->update([
                    'shopname' => $shopdata->nameOfStore
                ]);
            }
        }
        return response()->json(
            [
                'success' => 'data updated',
                'data' => $shopdata
            ]
        );
    }

    public function deleteshop(Request $request) // delete shop  using Auth::id after check that he has this shop by middleware and delete all products and the relationship between them
    {
        try {
            if ($this->validate_ID_request($request)->fails()) {
                return response()->json([
                    'error' => true,
                    'message' =>  $this->validate_ID_request($request)->errors()
                ]);
            }
            // $shop = Product::With('shops')->where('productId','=', 'HZ')->first();
            $shop = Shop::find($request->shopId);
            $deletproduct = $shop->products()->detach();  // remove relation between products and shop by id of shop
            $countofattach = $shop->products()->count();   // remove relation between products and shop by id of shop

            if ($deletproduct || $countofattach == 0) {


                $getshopidtodelete = Shop::findorfail($request->shopId);
                $getshopname =  $getshopidtodelete;


                $checkdelete = $getshopidtodelete->delete();
                if ($checkdelete) {
                    Product::where('shopname',   $getshopname->nameOfStore)->delete();

                    return response()->json([
                        'message' => 'This Shop was deleted'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'This Shop does not deleted'
                    ]);
                }
            }
        } catch (\Exception $ex) {
            return response()->json([
                [$ex->getCode(), $ex->getMessage()], 500
            ]);
        }
    }

    public  function showshop(Request $request) // each use has a shops will show by this function
    {

        try {
            $data = User::with('shops')->where('id',  auth::id())->get();
            if ($data[0]['shops'] == '[]') {
                $data[0]['shops']['message'] = 'You do not have any shops';
            }
            return response()->json([
                'data' => $data
                //   'Data'=> shop::with('owner')->get()  ->where('shopId',$request->shopId)
            ]);
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()], 500);
        }
    }
    public function showallshops() // All Shops data For Admin
    {
        $shops = Shop::all();
        // Loop through each shop and add owner name
        foreach ($shops as $shop) {
            $ownerId = $shop['ownerId'];
            $shopname = $shop['nameOfStore'];
            $productcount = Product::where('shopname', $shopname)->count();
            $shop['Number Of Products'] = $productcount;
            $shop['owner Data'] = User::where('id',  $ownerId)->get(['nameOfOwner as Name Of Owner', 'email', 'PhoneNumberOfOwner as Phone Number']);
        }
        if ($shops == '[]') {
            return response()->json(['All Shops data For Admin' => 'No Shops Added yet']);
        }
        return response()->json(['All Shops data For Admin' => $shops]);
    }
}
