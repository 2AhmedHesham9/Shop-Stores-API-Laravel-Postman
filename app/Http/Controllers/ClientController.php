<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    function validateClient(Request $request) //validate clientname email password phone number
    {
        $rules = [
            "clientName" => "required",
            "email" => "required|unique:client",
            "password" => "required",
            "clientPhoneNumber" => "required",
        ];

        $validator = Validator::make($request->all(), $rules);
        return $validator;
    }

    public function register(Request $request) //Register using => clientname email password phone number
    {
        try {

            if ($this->validateClient($request)->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $this->validateClient($request)->errors()], 422);
            }


            //register
            $store = Client::create([
                'clientName' =>  $request->clientName,
                'email' => $request->email,
                'password' => $request->password,
                'clientPhoneNumber' => $request->clientPhoneNumber,


            ]);
            if ($store) {

                return response()->json($store, 200);
            } else {
                return response()->json('No Data added');
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => true,
                'message' =>  $ex->getMessage()
            ]);
        }
    }
    public function login(Request $request) //Login and retrive JWT Token (using email password)
    {
        //validation
        try {
            $rules = [
                'email' => "required|exists:client|email",
                'password' => "required"

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $validator->errors()], 422);
            }
            //login

            $credentials = $request->only(['email', 'password']);

            $token = Auth::guard('client-api')->attempt($credentials);
            if (!$token) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            // Get the authenticated user
            $client = Auth::guard('client-api')->user();

            //return token and data
            return response()->json(['token' => $token, 'user' => $client], 200);
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()]);
        }
    }
    public function update(Request $request) //update client data but after check he is the owner of account
    {


        if ($this->validateClient($request)->fails()) {
            return response()->json(['error' => 'Invalid data', 'errors' => $this->validateClient($request)->errors()], 422);
        }
        $CheckUserHasThisAccount = Client::where('clientId', Auth::guard('client-api')->id())->first();
        if ($CheckUserHasThisAccount) {
            $CheckUserHasThisAccount->update([
                'clientName' => $request->clientName,
                'email' => $request->email,
                'password' => $request->password,
                'clientPhoneNumber' => $request->clientPhoneNumber,
            ]);
            return response()->json([$CheckUserHasThisAccount], 200);
        }
    }
    public function delete()  // Delete client but before you must delete related foreigns from order and invoice using detach and delete
    {


        try {
            $clientid = Auth::guard('client-api')->user()->clientId;
            $clientdata = Client::find($clientid);
            $client = Auth::guard('client-api')->user();
            $clientOrders = $client->orders;

            foreach ($clientOrders as $clientorder) {
                $order = Order::find($clientorder->id);
                // Detach products from order
                $order->products()->detach();

                // Detach invoices
                $order->invoice()->delete();
                $order->delete();
            }
            // Delte Client
            $clientdata->delete();

            return response()->json(['Message' => 'Your account deleted'], 200);
        } catch (\Exception $ex) {
            return response()->json([
                [$ex->getCode(), $ex->getMessage()], 500
            ]);
        }
    }
    public function show() //Show client data
    {
        return response()->json(
            [
                'Data' => Auth::guard('client-api')->user()
            ]
        );
    }

    public function logout() //logout and invalid token using   JWTAuth::invalidate(JWTAuth::getToken());
    {
        Auth::logout(); // Optional: Laravel default logout
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
