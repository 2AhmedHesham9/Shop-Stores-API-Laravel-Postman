<?php

namespace App\Http\Controllers;

use App\Services\Admin\AdminService;
use App\Http\Requests\LoginAdminRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Services\Shop\ShopService;

class AdminController extends Controller
{
    protected $adminService;
    protected $shopservice;
    public function __construct(AdminService $adminService, ShopService $shopservice)
    {
        $this->adminService = $adminService;
        $this->shopservice = $shopservice;
    }

    public function register(RegisterAdminRequest $request)
    {
        $response = $this->adminService->createAdmin($request);
        return response()->json($response['message'], $response['status']);
    }
    public function login(LoginAdminRequest $request)
    {
        $data = $this->adminService->adminLogin($request);
        //return token and data
        return response()->json($data['message'],  $data['status']);
    }
    public function showallshops()
    {
        $response = $this->shopservice->showallshops();
        return response()->json(["message" => $response['message'], "status" => $response['status']]);
    }
    public function logout()
    {
        return response()->json($this->adminService->logout());
    }
}
