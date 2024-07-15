<?php

namespace App\Http\Controllers;

use App\Services\Invoice\InvoiceService;


class InvoiceController extends Controller
{
    protected $invoiceservice;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceservice = $invoiceService;
    }
    public function storeinvoice($orderId, $total) // store invoice after store order and product_order tables it used at order table
    {
        $response = $this->invoiceservice->store($orderId, $total);
        return response()->json($response['message'], $response['status']);
    }
    public function revenue()   // return each shop with thier revenue
    {
        $response = $this->invoiceservice->getRevenue();
        return response()->json($response['message'], $response['status']);

    }
}
