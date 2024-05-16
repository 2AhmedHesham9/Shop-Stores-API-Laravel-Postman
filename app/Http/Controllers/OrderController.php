<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\InvoiceController;



class OrderController extends Controller
{
    protected $invoiceController;

    public function __construct(InvoiceController $invoiceService)
    {
        $this->invoiceController = $invoiceService;
    }
    public function makeorder(Request $request)  // make order => first check that products are available then make order then product_order finally invoice
    {


        // Create the order

        $avilable = $this->checkamountbyquantity($request)[0];
        $message = $this->checkamountbyquantity($request)[1];
        if ($avilable) {
            $clientid = Auth::guard('client-api')->id();
            $order = Order::create([
                'clientId' =>  $clientid,

            ]);


            if ($order) {
                $total = $this->storeOrderProducts($request, $order->id);
                if ($total) {
                    $this->invoiceController->storeinvoice($order->id, $total);
                }
            }

            // Return a response indicating success
            return response()->json(['order' => 'Order send successfully'], 201);
        } else {
            return  $message;
        }
    }

    public function storeOrderProducts(Request $request, $orderId) // it use at makeorder function
    {
        $total = 0;
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.productId' => 'required|exists:product,productId',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {

            return response()->json([$validator->errors()], 201);
        }
        // Find the order
        $order = Order::findOrFail($orderId);

        // Iterate over each product in the request

        foreach ($request->input('products') as $productData) {
            // Find the product
            $product = Product::findOrFail($productData['productId']);


            // Create the order product
            $orderProduct = new OrderProduct();

            $orderProduct->order_id =  $order->id;
            $orderProduct->product_id = $product->productId;

            $orderProduct->quantity = $productData['quantity'];

            $total +=  $product->price * $productData['quantity'];

            $orderProduct->save();

            $product->update([
                'amount' => $product->amount - $productData['quantity']
            ]);
        }
        return $total;
    }
    public function Showallorders() // get all orders for the login client
    {
        $clientid = Auth::guard('client-api')->id('clientId');
        $client = Client::Find($clientid);

        $orders = $client->orders;

        $objectsArrayinvoice = [];
        $objectsArrayProductsOrder = [];
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $getorder = Order::find($order->id);

                if (($getorder)) {
                    $invoice = $getorder->invoice;
                    $objectsArrayProductsOrder[] = $this->getarrayOfProducts($order->id);
                    $objectsArrayinvoice[] = $invoice;  //return all invoice

                }
            }

            return    $objectsArrayProductsOrder;
        }
        return response()->json(['Message' => 'You do not have any order!']);
    }
    public function checkamountbyquantity(Request $request) // it using in make order to ckeck if user required quantity more than amount in db
    {
        $avilable = 1;
        $message = '';
        foreach ($request->input('products') as $productData) {
            $product = Product::findOrFail($productData['productId']);
            if ($productData['quantity'] > $product->amount) {
                $this->$avilable = 0;
                $this->$message =  ['message' => 'You can not order more than ' . $product->amount . ' of ' . $product->name];


                // return $this->$message;
                return  [$this->$avilable, $this->$message];
            }
        }
        return   [$avilable, $avilable];
    }
    public function getarrayOfProducts($orderid)   // returns array of products name, price and quantity ,
    {
        $objectsArrayorderproduct = [];


        $order = Order::with('products')->find($orderid);
        $products = $order->products;
        foreach ($products as $product) {
            $productid = $product->order_product->product_id;
            $orderid = $product->order_product->order_id;
            $productget = Product::find($productid);
            $invoiceget = invoice::where('order_id', $orderid)->first();
            $objectsArrayorderproduct[] = [

                'Invoice Number' =>     $invoiceget->invoiceId,
                'Order Number' =>     $orderid,
                'Name Of Product' =>  $product->order_product['name'] = $productget->name,
                'Product Price' =>  $product->order_product['price'] = $productget->price,
                'Product quantity' =>  $product->order_product['quantity'] = $product->order_product->quantity,
                'Store Name' =>  $product['quantity'] = $productget->shopname,
                'Product created At' =>  $product->order_product['created_at'] = $product->order_product->created_at,
                'Product Updated At' =>  $product->order_product['updated_at'] = $product->order_product->updated_at
            ];

            // $objectsArrayorderproduct[] = $product->order_product;
        }
        $objectsArrayorderproduct['Total'] = $invoiceget->invoiceTotal . '$';    // insert total
        return   $objectsArrayorderproduct;
    }
    public function deleteorder(Request $request) // delete order but after detach from product table and then remove invoice then remove order
    {
        $validator = Validator::make($request->all(), [
            'orderid' => 'required|exists:orders,id'
        ]);
        if (!$validator->fails()) {

            $order = Order::find($request->orderid);
            $clientid = Client::Where('clientId', $order->clientId)->first();
            $authid = Auth::guard('client-api')->id('clientId');


            if ($clientid->clientId == $authid) {

                $order->products()->detach();

                // Detach invoices
                $order->invoice()->delete();

                // Delete the order itself
                $order->delete();

                return response()->json(['Message' => 'Order Deleted Successfullly']);
            } else {
                return response()->json(['Message' => 'This Order Not For You']);
            }
        } else {
            return response()->json($validator->errors());
        }
    }
}
