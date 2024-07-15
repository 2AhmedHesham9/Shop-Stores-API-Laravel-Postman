<?php

namespace App\Http\Controllers;

use App\Http\Requests\client\LoginClientRequest;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Services\Client\ClientService;

use App\Http\Requests\client\StoreClientRequest;
use App\Http\Requests\client\UpdateClientRequest;


class ClientController extends Controller
{
    protected $clientservice;
    public function __construct(ClientService $clientservice)
    {
        $this->clientservice = $clientservice;
    }
    public function register(StoreClientRequest $request) //Register using => clientname email password phone number
    {
        $response = $this->clientservice->register($request);
        return response()->json($response['message'], $response['status']);
    }
    public function login(LoginClientRequest $request) //Login and retrive JWT Token (using email password)
    {
        $response = $this->clientservice->login($request);
        return response()->json($response['message'], $response['status']);
    }
    public function update(UpdateClientRequest $request) //update client data but after check he is the owner of account
    {
        $response = $this->clientservice->update($request);
        return response()->json($response['message'], $response['status']);
    }
    public function delete()  // Delete client but before you must delete related foreigns from order and invoice using detach and delete
    {
        $response = $this->clientservice->delete();
        return response()->json($response['message'], $response['status']);
    }
    public function show() //Show client data
    {
        $response = $this->clientservice->getAuthClientData();
        return response()->json($response['message'], $response['status']);
    }
    public function restore(LoginClientRequest $request)
    {
        $response = $this->clientservice->restore($request);
        return response()->json($response['message'], $response['status']);
    }
    public function logout() //logout and invalid token using   JWTAuth::invalidate(JWTAuth::getToken());
    {
        Auth::logout(); // Optional: Laravel default logout
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
