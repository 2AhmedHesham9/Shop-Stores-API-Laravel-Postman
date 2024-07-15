<?php

namespace  App\Services\Order;

use App\Models\Order;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\Auth;
use App\Services\Invoice\InvoiceService;
use App\Services\Product\ProductService;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Services\OrderProduct\OrderProductService;
use App\Http\Requests\OrderProduct\CreatOrderProductRequest;
use App\Services\Client\ClientService;

class OrderService
{
    protected $productservice;
    protected $orderproductservice;
    protected $invoiceservice;
    protected $clientservice;
    public function __construct(ProductService $productservice, OrderProductService $orderproductservice, InvoiceService $invoiceService, ClientService $clientservice)
    {
        $this->productservice = $productservice;
        $this->orderproductservice = $orderproductservice;
        $this->invoiceservice = $invoiceService;
        $this->clientservice = $clientservice;
    }
    function makeOrder(CreatOrderProductRequest $request)
    {
        $response = $this->productservice->checkProductAmountAvilability($request);
        $avilable = $response[0];
        $message = $response[1];
        if ($avilable) {

            $clientid = Auth::guard('client-api')->id();
            $order = Order::create([
                'clientId' =>  $clientid,
            ]);
            if ($order) {
                $total =  $this->orderproductservice->createOrderProducts($request, $order->id);
                if ($total) {
                    $this->invoiceservice->store($order->id, $total);
                }
            }

            // Return a response indicating success
            return  ['message' => 'Order send successfully', "status" => 201];
        }

        return  ['message' =>   $message, "status" => 201];
    }
    public function showMyOrders()
    {
        try {
            $clientid = Auth::guard('client-api')->id('clientId');
            $client = Client::Find($clientid);
            $orders = $client->orders;
            $objectsArrayinvoice = [];
            $objectsArrayProductsOrder = [];

            if ($orders->count() > 0) {

                foreach ($orders as $order) {
                    $order->load('invoice'); //use relationship
                    $invoice =  $order->invoice;
                    $objectsArrayProductsOrder[] = $this->productservice->getProductShopDataForOrder($order->id);
                    $objectsArrayinvoice[] = $invoice;  //return all invoice
                }
                return   ['message' => $objectsArrayProductsOrder, 'status' => 200];
            }
            return  ['message' => 'You did not make any order!', 'status' => 200];
        } catch (\Exception $e) {
            return ['message' => $e->getMessage(), 'status' =>  $e->getCode()];
        }
    }
    public function cancelOrder(CancelOrderRequest $request)
    {
        try {
            $orderId = $request->orderid;
            $order = $this->getOrder($orderId);

            if (!$order) {
                return ['message' => 'Order not found', 'status' => 404];
            }

            $client =  $this->clientservice->getClient($order->clientId);
            $clientAuth = $this->clientservice->checkClientAuthorization($client);
            if (!$clientAuth) {
                return ['message' => 'This Order is not for you', 'status' => 403];
            }

            $orderproductData = $this->orderproductservice->getOrderProductByOrderId($order->id);
            $this->productservice->updateProductAmount($orderproductData);
            $this->orderproductservice->deleteOrderProducts($order->id);
            $this->invoiceservice->deleteInvoice($order);
            $order->delete();
            return  ['message' => 'Order Deleted Successfully', "status" => 200];
        } catch (\Exception $e) {
            return ["message" => $e->getMessage(), "status" => $e->getCode()];
        }
    }
    public function getOrder($orderId)
    {
        return Order::find($orderId);
    }
    public function removeOrders($Orders)
    {
        foreach ($Orders as $order) {
            // OrderProduct::where('order_id', '=', $order->id)->delete();
            $order->invoice()->delete();
            $order->delete();
        }
    }
}
