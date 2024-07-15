<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Product\ProductService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\product\GetProductRequest;
use App\Http\Requests\product\StoreProductRequest;
use App\Http\Requests\product\DeleteProductRequest;
use App\Http\Requests\product\UpdateProductRequest;


class ProductController extends Controller
{
    protected $productservice;
    public function __construct(ProductService $productService)
    {
        $this->productservice = $productService;
    }

    public  function storeproduct(StoreProductRequest $request) //   Add product to store
    {
        $response = $this->productservice->addProduct($request);
        return response()->json($response['message'], $response['status']);
    }

    public function showAllProductOfEachShop() //  Show all products of each shop
    {
        $response =   $this->productservice->getAllProductsForEachShop();
        return response()->json($response['message'], $response['status']);
    }
    public function showAllProductOfShopById(GetProductRequest $request) // Show all products of shop by Id
    {

        $response = $this->productservice->getProductsForSpecificShop($request);
        return response()->json($response['message'], $response['status']);
    }
    public function DeleteProduct(DeleteProductRequest $request) // delete  product by id and shop name after delete relation between shop and this product
    {
        $response = $this->productservice->deleteProduct($request);
        return response()->json($response['message'], $response['status']);
    }

    public function updateproduct(UpdateProductRequest $request) // update product using id
    {
        $updateresponse = $this->productservice->updateProduct($request);
        return response()->json($updateresponse['message'], $updateresponse['status']);
    }
}
