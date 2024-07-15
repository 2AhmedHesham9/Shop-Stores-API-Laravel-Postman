<?php


namespace  App\Services\Invoice;

use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;

use App\Services\Product\ProductService;
use App\Services\Shop\ShopService;


class InvoiceService
{
    protected $shopservice;
    protected $productservice;
    public function __construct(ShopService $shopservice, ProductService $productservice)
    {
        $this->shopservice = $shopservice;
        $this->productservice = $productservice;
    }
    public function store($orderId, $total)
    {

        $order = Order::findOrFail($orderId);

        $invoice = new Invoice();
        $invoice->invoiceTotal = $total;
        $order->invoice()->save($invoice);

        $invoice->save();

        return ['message' => $invoice, 'status' => 201];
    }
    public function deleteInvoice($order)
    {
        $order->invoice()->delete();
    }
    private function getInvoiceTotal($orderid)
    {
        return Invoice::where('order_id', $orderid)->first()->invoiceTotal;
    }
    private function calculateTotalInvoiceForShop($shopname)
    {
        $total = 0;
        $products = $this->productservice->getProductsForShop($shopname);
        if ($products->isNotEmpty()) {
            foreach ($products as $product) {
                $productsorder = $product->orders;
                foreach ($productsorder as $productorder) {
                    $orderid =  $productorder->order_product->order_id;
                    // $invoiceTotal = Invoice::where('order_id', $orderid)->first()->invoiceTotal;
                    $invoiceTotal = $this->getInvoiceTotal($orderid);
                    $total +=  $invoiceTotal;
                }
            }
        } else {
            $this->$total = 0;
        }
        return $total;
    }
    private function getArrayOfRevenues($shopnames)
    {
        foreach ($shopnames as $shopname) {

            $total = $this->calculateTotalInvoiceForShop($shopname);

            $ArraynameRevenue[] =
                [
                    'Owner Name' => User::where('id', $shopname->ownerId)->first('nameOfOwner')['nameOfOwner'],
                    'Owner Email' => User::where('id', $shopname->ownerId)->first('email')['email'],
                    'Shop Name' => $shopname->nameOfStore,
                    'Revenue' => $total . '$'
                ];
        }
        return $ArraynameRevenue;
    }
    public function getRevenue()
    {
        $ArraynameRevenue = [];
        try {
            $shopnames = $this->shopservice->getShopsForAuth();

            if ($shopnames->isNotEmpty()) {
                $ArraynameRevenue =  $this->getArrayOfRevenues($shopnames);
                return  ['message' => $ArraynameRevenue, 'status' => 200];
            }
            return  ['message' => 'You do not have any shop', 'status' => 404];
        } catch (\Exception $ex) {
            return ["message" => $ex->getMessage(), "status" => $ex->getCode()];
        }
    }
}
