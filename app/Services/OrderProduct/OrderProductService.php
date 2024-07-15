<?php

namespace  App\Services\OrderProduct;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Http\Requests\OrderProduct\CreatOrderProductRequest;

class OrderProductService
{
    public function __construct()
    {
    }
    public function createOrderProducts(CreatOrderProductRequest $request, $orderId)
    {
        $total = 0;


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
    public function deleteOrderProducts($orderId)
    {
        OrderProduct::where('order_id', $orderId)->delete();
    }
    public function getOrderProductByOrderId($orderId)
    {
        return OrderProduct::where('order_id', $orderId)->first();
    }
}
