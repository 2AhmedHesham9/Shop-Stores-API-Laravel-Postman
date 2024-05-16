<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Js;

class ProductController extends Controller
{
    public function  CheckUserHasThisShop(Request $request)
    {
        $CheckUserHasThisShop = shop::where('ownerId', Auth::id())->where('nameOfStore', '=', $request->nameOfStore)->first();
        return $CheckUserHasThisShop;
    }
    public function validaterequest(Request $request)  // name price amount shopname
    {
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string',
                'price' => 'required|numeric',
                'amount' => 'required|integer',
                'nameOfStore' => 'required|string|exists:shop,nameOfStore',
            ]

        );
        return $validator;
    }
    public function validaterequestShopName(Request $request)  //    validator name
    {
        $validator = Validator::make(
            $request->all(),
            [



                'nameOfStore' => 'required|string|exists:shop,nameOfStore',
            ]

        );
        return $validator;
    }
    public function validaterequestProductID_ShopName(Request $request)  //   validator id for product and name for shop
    {
        $validator = Validator::make(
            $request->all(),
            [
                'productId' => 'required|numeric|exists:product,productId',
                'nameOfStore' => 'required|string|exists:shop,nameOfStore',
            ]

        );
        return $validator;
    }
    public function validateProductID(Request $request) //    validator product id
    {
        $validateProductID = Validator::make(
            $request->all(),
            [
                'productId' => 'required|numeric|exists:product,productId',
            ]

        );

        return   $validateProductID;
    }
    public  function storeproduct(Request $request) //   Add product to store
    {
        try {

            if (!$this->validaterequest($request)->fails()) {
                // Handle file upload
                $image = $request->file('image');
                //Get image file from request and delete the spaces then rename it to name of product + time + extenssion
                $imageName =  str_replace(' ', '', $request->name . time() . '.' . $image->getClientOriginalExtension());
                // create folder at public -> product/images to save each image to it
                $path = 'product/images/';
                $image->move(public_path($path), $imageName);

                $store = Product::create(
                    [
                        'image' => $path . $imageName,
                        'name' => $request->name,
                        'price' => $request->price,
                        'amount' => $request->amount,
                        'shopname' => $request->nameOfStore,

                    ]
                );

                if ($store) {
                    $productId = Product::Max('productId');   //get id for the last product added to the tabel
                    $product = Product::where('productId', $productId)->first();
                    $shop = shop::where('nameOfStore', $request->nameOfStore)->first();
                    // $shopId=Shop::where('shopname', $request->shopname)->id();   //get id for the last product added to the tabel

                    $shop->products()->syncWithoutDetaching($product);   //attach the product to the shop to fill the third tabel

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

    public function showAllProductOfEachShop() //  Show all products of each shop
    {

        $shop = Shop::With('products')->where('ownerId', '=', Auth::id())->get();  //return each shop with all products for the auth user
        if ($shop != '[]') {
            return response()->json([
                'message' => 'sucess',
                'Data' =>  $shop
            ]);
        }
        return response()->json([
            'message' => 'sucess',
            'Data' =>  'No Data To Display'
        ]);
    }
    public function showAllProductOfEachShopByName(Request $request) // Show all products of shop by name
    {
        if (!$this->validaterequestShopName($request)->fails()) {

            $shop = Shop::With('products')->get()->where('nameOfStore', $request->nameOfStore)->first(); //return specific shop with all products

            if ($shop->products->isNotEmpty()) {

                return response()->json([
                    'message' => 'sucess',
                    'Data' =>  $shop
                ]);
            }
            return response()->json([
                'message' => 'sucess',
                'Data' =>  "No Products Found"
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' =>  $this->validaterequestShopName($request)->errors()
            ], 404);
        }
    }
    public function DeleteProduct(Request $request) // delete  product by id and shop name after delete relation between shop and this product
    {

        if (!$this->validaterequestProductID_ShopName($request)->fails()) {

            $shop = Shop::With('products')->where('nameOfStore', $request->nameOfStore)->first();
            $deletproduct = $shop->products()->detach([$request->productId]);   // remove relation between products and shop by id of product

            if ($deletproduct) {
                $productid = Product::findOrfail($request->productId);

                if (File::exists($productid->image)) {
                    File::delete($productid->image);
                }
                $productid->delete();           // remove product if relation removed
                return response()->json([
                    'message' => 'This product deleted successfully',
                ]);
            } else {
                return response()->json([
                    'message' => 'This product does not exist in this store',

                ]);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' =>  $this->validaterequestProductID_ShopName($request)->errors()
            ], 404);
        }
    }

    public function updateproduct(Request $request) // update product using id
    {
        if ($this->validateProductID($request)->fails()) {
            return response()->json([

                $this->validateProductID($request)->errors()
            ]);
        }

        $product = Product::findOrFail($request->productId);
        if (!$this->validaterequest($request)->fails()) {


            $image = $request->file('image');
            //Get image file from request and delete the spaces then rename it to name of product + time + extenssion
            $imageName =  str_replace(' ', '', $request->name . time() . '.' . $image->getClientOriginalExtension());
            // create folder at public -> product/images to save each image to it
            $path = 'product/images/';
            $image->move(public_path($path), $imageName);
            if (File::exists($product->image)) {
                File::delete($product->image);
            }

            $product->update(
                [
                    'image' => $path . $imageName,
                    'name' => $request->name,
                    'price' => $request->price,
                    'amount' => $request->amount,
                    'shopname' => $request->nameOfStore,

                ]
            );
            return response()->json($product);
        } else {
            return response()->json([
                'error' => true,
                'message' =>  $this->validaterequest($request)->errors()
            ]);
        }
    }
}
