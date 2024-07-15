<?php

namespace App\Http\Controllers;


use App\Services\Order\OrderService;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\OrderProduct\CreatOrderProductRequest;



class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $OrderService)
    {
        $this->orderService = $OrderService;
    }
    public function makeorder(CreatOrderProductRequest $request)  // make order => first check that products are available then make order then product_order finally invoice
    {
        $response = $this->orderService->makeorder($request);
        return response()->json($response['message'], $response['status']);
    }

    public function Showallorders() // get all orders for the login client
    {
        $response = $this->orderService->showMyOrders();
        return response()->json($response['message'], $response['status']);
    }



    public function deleteorder(CancelOrderRequest $request) // delete order but after detach from product table and then remove invoice then remove order
    {
        $response = $this->orderService->cancelOrder($request);
        return response()->json($response['message'], $response['status']);
    }
}
