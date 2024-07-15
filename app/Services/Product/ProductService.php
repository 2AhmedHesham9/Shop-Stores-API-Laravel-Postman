<?php


namespace App\Services\Product;

use App\Models\Shop;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Requests\product\GetProductRequest;
use App\Http\Requests\product\StoreProductRequest;
use App\Http\Requests\product\DeleteProductRequest;
use App\Http\Requests\product\UpdateProductRequest;
use App\Http\Requests\OrderProduct\CreatOrderProductRequest;

class ProductService
{
    private function handelImageToStore(StoreProductRequest $request)
    {
        $image = $request->file('image');
        //Get image file from request and delete the spaces then rename it to name of product + time + extenssion
        $imageName =  str_replace(' ', '', $request->name . time() . '.' . $image->getClientOriginalExtension());
        // create folder at public -> product/images to save each image to it
        $path = 'product/images/';
        $image->move(public_path($path), $imageName);
        return $path . $imageName;
    }
    private function handelImageToUpdate(UpdateProductRequest $request, $product)
    {
        $image = $request->file('image');

        $imageName =  str_replace(' ', '', $request->name . time() . '.' . $image->getClientOriginalExtension());

        $path = 'product/images/';
        $image->move(public_path($path), $imageName);
        if (File::exists($product->image)) {
            File::delete($product->image);
        }
        return $path . $imageName;
    }
    private function deleteimage($product)
    {
        if (File::exists($product->image)) {
            File::delete($product->image);
        }
    }
    private function checkProsuctInShop($productid, $shopid)
    {
        $response = Product::Where('productid', $productid)->where('shopId', $shopid)->first();
        if ($response) {
            return 1;
        }
        return 0;
    }
    public function addProduct(StoreProductRequest $request)
    {
        try {
            $image = $this->handelImageToStore($request);
            Product::create(
                [
                    'image' => $image,
                    'name' => $request->name,
                    'price' => $request->price,
                    'amount' => $request->amount,
                    'shopId' => $request->shopId,

                ]
            );
            return [
                "message" => "Product added successfully!",
                "status" => 200
            ];
        } catch (\Exception $ex) {
            return ["message" =>  'error: ' . $ex->getMessage(), "status" => $ex->getCode()];
        }
    }

    public function deleteProduct(DeleteProductRequest $request)
    {
        try {

            $shophasProduct =  $this->checkProsuctInShop($request->productId, $request->shopId);
            if (!$shophasProduct) {
                return [
                    "message" => "You do not have this product in your Shop!",
                    "status" => 404
                ];
            }
            $shop = Shop::With('products')->where('shopId', $request->shopId)->get();
            if ($shop) {
                $product = Product::findOrfail($request->productId);
                $this->deleteimage($product);
                $product->delete();
                return [
                    'message' => 'This product deleted successfully',
                    'status' => 200
                ];
            }
        } catch (\Exception $ex) {
            return ["message" =>  'error: ' . $ex->getMessage(), "status" => $ex->getCode()];
        }
    }

    public function updateProduct(UpdateProductRequest $request)
    {
        try {

            $shophasProduct =  $this->checkProsuctInShop($request->productId, $request->shopId);
            if (!$shophasProduct) {
                return [
                    "message" => "You do not have this product in your Shop!",
                    "status" => 404
                ];
            }
            $product = Product::findOrFail($request->productId);
            $newImage =  $this->handelImageToUpdate($request, $product);
            $product->update(
                [
                    'image' => $newImage,
                    'name' => $request->name,
                    'price' => $request->price,
                    'amount' => $request->amount,
                ]
            );
            return [
                "message" => "Product Updated successfully!",
                "status" => 200
            ];
        } catch (\Exception $ex) {
            return [
                "message" => $ex->getMessage(),
                "status" =>  $ex->getCode()
            ];
        }
    }

    public function getAllProductsForEachShop()
    {
        try {
            $shop = Shop::With('products')->where('ownerId', '=', Auth::id())->get();  //return each shop with all products for the auth user
            if ($shop != '[]') {
                return [
                    'message' =>   $shop,
                    'status' => 200
                ];
            }
            return [
                'message' => 'No Data To Display',
                'status' => 200
            ];
        } catch (\Exception $ex) {
            return [
                'message' => $ex->getMessage(),
                'status' => $ex->getCode()
            ];
        }
    }
    public function getProductsForSpecificShop(GetProductRequest $request) //!test this
    {
        try {
            $shop = Shop::With('products')->get()->where('shopId', $request->shopId)->first(); //return specific shop with all products

            if ($shop->products->isNotEmpty()) {

                return  [
                    'message' =>  $shop,
                    'status' => 200
                ];
            }

            return  [
                'message' =>  "No Products Found",
                'status' => 404
            ];
        } catch (\Exception $ex) {
            return [
                'message' => $ex->getMessage(),
                'status' => $ex->getCode()
            ];
        }
    }
    function restoreProducts($shops)
    {
        foreach ($shops as $shop) {
            Product::withTrashed()->where('shopId', $shop->shopId)->restore();
        }
    }
    public function checkProductAmountAvilability(CreatOrderProductRequest $request) //used in orderservice makeOrder function
    {
        $avilable = 1;
        $message = '';
        foreach ($request->input('products') as $productData) {
            $product = Product::findOrFail($productData['productId']);
            if ($productData['quantity'] > $product->amount) {
                $this->$avilable = 0;
                $this->$message =  ['message' => 'You can not order more than ' . $product->amount . ' of ' . $product->name];

                return  [$this->$avilable, $this->$message];
            }
        }
        return   [$avilable, $avilable];
    }
    public function getProductShopDataForOrder($orderid)  // used in orderService getMyOrder function
    {
        $objectsArrayorderproduct = [];


        $order = Order::with('products')->find($orderid);
        $products = $order->products;

        foreach ($products as $product) {
            $productid = $product->order_product->product_id;
            $orderid = $product->order_product->order_id;
            $getproduct = Product::find($productid);
            $invoiceget = Invoice::where('order_id', $orderid)->first();
            $shop = Shop::find($getproduct->shopId);
            $objectsArrayorderproduct[] = [

                'Invoice Number'     =>  $invoiceget->invoiceId,
                'Order Number'       =>  $orderid,
                'Name Of Product'    =>  $product->order_product['name'] = $getproduct->name,
                'Product Price'      =>  $product->order_product['price'] = $getproduct->price,
                'Product quantity'   =>  $product->order_product['quantity'] = $product->order_product->quantity,
                'Store Name'         =>  $product['shopname'] =  $shop->nameOfStore,
                'Product created At' =>  $product->order_product['created_at'] = $product->order_product->created_at,
                'Product Updated At' =>  $product->order_product['updated_at'] = $product->order_product->updated_at
            ];

            // $objectsArrayorderproduct[] = $product->order_product;
        }
        $objectsArrayorderproduct['Total'] = $invoiceget->invoiceTotal . '$';    // insert total
        return   $objectsArrayorderproduct;
    }
    public function updateProductAmount($orderproductData)
    {
        $product = Product::find($orderproductData->product_id);
        $product->update(
            [
                'amount' => $product->amount + $orderproductData->quantity,
            ]

        );
    }
    public function deleteProductsForSpecificShop($shop)
    {
        return Product::Where('shopId', '=',  $shop->shopId)->delete();
    }
    public function getCountOfProductsForSpecificShop($shop)
    {
        return  $shop->products->count();
    }
    public function getProductsForShop($shopname) // used in invoiceservice
    {
        return Product::where('shopId', $shopname->shopId)->get();
    }
}
