<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function register(Request $request)
    {


        try {
            $rules = [
                "name" => "required",
                "email" => "required|unique:websiteadmin",
                "password" => "required",
                // "role" => "required",
                "phonenumber" => "required",



            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            //register
            $store = Admin::create([
                'name' =>  $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role,
                'phonenumber' => $request->phonenumber,


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
                'email' => "required|exists:websiteadmin|email",
                'password' => "required"

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid data', 'errors' => $validator->errors()], 422);
            }
            //login

            $credentials = $request->only(['email', 'password']);
            // Get Token
            $token = Auth::guard('admin-api')->attempt($credentials);

            if (!$token) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            // Get the authenticated user
            $user = Auth::guard('admin-api')->user();

            //return token and data
            return response()->json(['token' => $token, 'user' => $user], 200);
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()]);
        }
    }
}
