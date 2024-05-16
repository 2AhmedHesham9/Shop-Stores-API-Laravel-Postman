<?php

namespace App\Http\Controllers;


use App\Models\Shop;
use App\Models\User;

use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller  //user controller
{



    function validateOwner(Request $request)
    {
        $rules = [
            "nameOfOwner" => "required",
            "email" => "required|unique:users",
            "password" => "required",
            "PhoneNumberOfOwner" => "required",



        ];
        $validator = Validator::make($request->all(), $rules);
        return $validator;
    }
    public function register(Request $request)
    {


        try {

            if ($this->validateOwner($request)->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $this->validateOwner($request)->errors()], 422);
            }

            // $user = Auth::user();
            //register
            $store = User::create([
                'nameOfOwner' =>  $request->nameOfOwner,
                'email' => $request->email,
                'password' => $request->password,
                'PhoneNumberOfOwner' => $request->PhoneNumberOfOwner,


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

    public function login(Request $request)
    {
        //validation
        try {
            $rules = [
                'email' => "required|exists:users|email",
                'password' => "required"

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $validator->errors()], 422);
            }
            //login

            $credentials = $request->only(['email', 'password']);

            //  return response()->json([   $credentials], 401);

            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            // Get the authenticated user
            $user = Auth::guard('api')->user();

            //return token and data
            return response()->json(['token' => $token, 'user' => $user], 200);
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()]);
        }
    }
    public function updateowner(Request $request)
    {
        $CheckUserHasThisAccount = User::where('id', Auth::id())->first();


        if ($this->validateOwner($request)->fails()) {
            return response()->json(['error' => 'Invalid data', 'errors' => $this->validateOwner($request)->errors()], 422);
        }
        if ($CheckUserHasThisAccount) {
            $CheckUserHasThisAccount->update([
                'nameOfOwner' => $request->nameOfOwner,
                'email' => $request->email,
                'password' => $request->password,
                'PhoneNumberOfOwner' => $request->PhoneNumberOfOwner,


            ]);
            return response()->json([$CheckUserHasThisAccount], 200);
        }
    }
    public function deleteowner()
    {


        try {
            $ownerid = Auth::user()->id;
            $user = User::Find(Auth::id());
            // Retrieve the shops owned by the user
            $shops = Shop::where('ownerId', $ownerid)->get();



            // $shop = Product::With('shops')->where('productId','=', 'HZ')->first();
            foreach ($shops as $shop) {
                $shop = Shop::find($shop->shopId);
                $shop->products()->detach();   // remove relation between products and shop by id of shop
                $getshopidtodelete = Shop::findorfail($shop->shopId);
                $checkdelete = $getshopidtodelete->delete();
                if (!$checkdelete) {
                    return response()->json([
                        'message' => 'This Shop does not deleted'
                    ]);
                }
            }
            if ($user->delete()) {
                return response()->json(['message' => 'User Deleted Successfully'], 200);
            }
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $ex) {
            return response()->json([
                [$ex->getCode(), $ex->getMessage()], 500
            ]);
        }
    }


    public function logout(Request $request)
    {


        // $user = Auth::user();

        Auth::logout(); // Optional: Laravel default logout
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
