<?php

namespace App\Services\Client;

use App\Models\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\client\LoginClientRequest;
use App\Http\Requests\client\StoreClientRequest;
use App\Http\Requests\client\UpdateClientRequest;

class ClientService
{
    protected $orderservice;
    public function __construct(OrderService $orderservice)
    {
        $this->orderservice = $orderservice;
    }
    public function register(StoreClientRequest $request)
    {
        try {
            Client::create([
                'clientName' =>  $request->clientName,
                'email' => $request->email,
                'password' => $request->password,
                'clientPhoneNumber' => $request->clientPhoneNumber,
            ]);
            return ["message" => "Register Success", "status" => 200];
        } catch (\Exception $ex) {
            return  [
                'message' =>  $ex->getMessage(),
                'status' => $ex->getCode()
            ];
        }
    }
    public function login(LoginClientRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $token = Auth::guard('client-api')->attempt($credentials);
            if (!$token) {

                return ["message" => 'Invalid credentials', "status" => 401];
            }
            // Get the authenticated user
            $client = Auth::guard('client-api')->user();
            $client->token = $token;
            //return token and data
            return ["message" => $client, "status" => 200];
        } catch (\Exception $ex) {
            return  ['message' => $ex->getMessage(), "status" => 500];
        }
    }
    public function getAuthClientData()
    {
        $clientData = Auth::guard('client-api')->user();
        return
            [
                'message' => $clientData,
                "status" => 200
            ];
    }
    public function update(UpdateClientRequest $request)
    {
        try {
            $Client = Client::where('clientId', Auth::guard('client-api')->id())->first();
            $Client->update([
                'clientName' => $request->clientName,
                'email' => $request->email,
                'password' => $request->password,
                'clientPhoneNumber' => $request->clientPhoneNumber,
            ]);
            return ["message" => $Client, "status" => 200];
        } catch (\Exception $ex) {
            return  ['message' => $ex->getMessage(), "status" => 500];
        }
    }
    public function delete()
    {
        // ^ should i delete all data related to client?
        try {
            $client = Auth::guard('client-api')->user();
            $clientdata = Client::find($client->clientId);
            // $Orders = $client->orders;
            // delete all related orders with this client
            // $this->orderservice->removeOrders($Orders);
            // Delete Client
            $clientdata->delete();
            $this->logout();
            return  ['message' => 'Your account is deleted', 'status' => 200];
        } catch (\Exception $ex) {
            return  ['message' => $ex->getMessage(), "status" => 500];
        }
    }
    public function restore(LoginClientRequest $request)
    {
        //& Find the user by email, including soft-deleted users
        $client = Client::withTrashed()->where('email', $request->email)->first();

        //^ Restore the user if they are soft-deleted and the password is correct
        $userpassword = Hash::check($request->password, $client->password);
        if ($client->trashed() && $userpassword) {
            $client->restore();
            $client->save();
            return  ['message' => 'Account restore successfully.', "status" => 200];
        }
        if (!$client->trashed()) {
            return  ['message' => 'Your Account was restored successfully.', "status" => 200];
        }
        return  ['message' => 'Password incorrect', "status" => 401];
    }
    public function getClient($clientId)
    {
        return Client::where('clientId', $clientId)->first();
    }
    public function checkClientAuthorization($client)
    {
        $authenticatedClientId  = Auth::guard('client-api')->id('clientId');
        return $client->clientId == $authenticatedClientId;
    }
    public function logout()
    {
        try {
            Auth::guard('client-api')->logout(); // Optional: Laravel default logout
            JWTAuth::invalidate(JWTAuth::getToken());
            return  ['message' => 'User logged out successfully', 'status' => 200];
        } catch (\Exception $ex) {
            return ['message' => $ex->getMessage(), 'status' => $ex->getCode()];
        }
    }
}
