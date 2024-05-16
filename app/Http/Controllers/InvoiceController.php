<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;

use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function storeinvoice($orderId, $total) // store invoice after store order and product_order tables it used at order table
    {
        // Find the order
        $order = Order::findOrFail($orderId);



        // Create the invoice
        $invoice = new Invoice();


        $invoice->invoiceTotal = $total;
        $order->invoice()->save($invoice);
        $invoice->save();

        // Return a response indicating success
        return response()->json(['invoice' => $invoice], 201);
    }
    public function revenue()   // return each shop with thier revenue
    {
        $total = 0;
        $ArraynameREvenue = [];
        $authid = Auth::id();
        $ownerid = User::where('id', Auth::id())->get('id');
        $shopnames = Shop::where('ownerId', Auth::id())->get();
        if ($shopnames->count() > 0) {
            foreach ($shopnames as $shopname) {

                $shopname->nameOfStore;
                $products = Product::where('shopname', $shopname->nameOfStore)->get();

                if ($products->count() > 0) {
                    foreach ($products as $product) {
                        $productsorder = $product->orders;
                        foreach ($productsorder as $productorder) {

                            $orderid =  $productorder->order_product->order_id;
                            $invoiceTotal = invoice::where('order_id', $orderid)->first()->invoiceTotal;
                            $total +=  $invoiceTotal;
                        }
                    }
                } else {
                    $this->$total = 0;
                }


                $ArraynameREvenue[] =
                    [
                        'Shop Name' => $shopname->nameOfStore,
                        'Revenue' => $total
                    ];

                $total = 0;
            }
            return response()->json(['Shops' => $ArraynameREvenue], 200);
        } else {
            return response()->json(['Shops' => 'You do not have any shop'], 404);
        }
    }
}
