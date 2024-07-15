<?php

namespace App\Services\Admin;

use App\Http\Requests\LoginAdminRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Models\User;
use App\Models\Admin;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;



class AdminService
{
    public function createAdmin(RegisterAdminRequest $request)
    {
        try {
            Admin::create([
                'name' =>  $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role,
                'phonenumber' => $request->phonenumber,
            ]);

            return ["message" => "Register Success", "status" => 200];
        } catch (\Exception $e) {
            // Other exceptions
            return   ['message' =>  $e->getMessage(), "status" =>  $e->getCode()];
        }
    }
    public function adminLogin(LoginAdminRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            // Get Token
            $token = Auth::guard('admin-api')->attempt($credentials);
            if (!$token) {
                return   ['message' => 'Invalid Credentials', "status" =>  401];
            }
            // Get the authenticated user
            $user = Auth::guard('admin-api')->user();
            $user->token = $token;
            return   ['message' => $user, "status" =>  200];
           
        } catch (\Exception $ex) {
            return   ['message' =>  $ex->getMessage(), "status" =>  $ex->getCode()];
        }
    }

    public function logout()
    {
        Auth::logout(); // Optional: Laravel default logout
        JWTAuth::invalidate(JWTAuth::getToken());
        return  ['message' => 'User logged out successfully'];
    }
}
