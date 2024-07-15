<?php

namespace App\Services\Owner;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginOwnerRequest;
use App\Services\Product\ProductService;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Requests\RegisterOwnerRequest;
use App\Models\Product;
use App\Services\Shop\ShopService;

class OwnerService
{
    private $productservice;
    private $shopservice;
    public function __construct(ProductService $productservice, ShopService $shopservice)
    {
        $this->productservice = $productservice;
        $this->shopservice = $shopservice;
    }
    public function createOwner(RegisterOwnerRequest $request)
    {
        try {
            User::create([
                'nameOfOwner' =>  $request->nameOfOwner,
                'email' => $request->email,
                'password' => $request->password,
                'PhoneNumberOfOwner' => $request->PhoneNumberOfOwner,
            ]);
            return [
                "message" => "Register Success",
                "status" => 200
            ];
        } catch (\Exception $e) {
            return   ['message' => 'Failed to create Owner :' . $e->getMessage(), "status" => 500];
        }
    }
    public function LoginOwner(LoginOwnerRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return  ['data' => 'Invalid credentials', "status" => 401];
            }
            $user = Auth::guard('api')->user();
            $user->token = $token;
            return ["data" => $user, "status" => 200];
        } catch (\Exception $ex) {
            return  ['data' => $ex->getMessage(), "status" => 500];
        }
    }
    public function updateOwner(UpdateOwnerRequest $request)
    {
        try {
            $getUserAuthData = User::where('id', Auth::id())->first();
            if ($getUserAuthData) {
                $getUserAuthData->update([
                    'nameOfOwner' => $request->nameOfOwner,
                    'email' => $request->email,
                    'password' => $request->password,
                    'PhoneNumberOfOwner' => $request->PhoneNumberOfOwner,
                ]);
                return ["data" => $getUserAuthData, "status" => 200];
            }
        } catch (\Exception $ex) {
            return ['data' => $ex->getMessage(), 'status' => $ex->getCode()];
        }
    }
    public function deleteOwner()
    {

        try {
            $owner = User::Find(Auth::id());
            $this->deleteRelatedDataForOwner();
            if ($owner->delete()) {
                $this->logoutOwner();
                return  [
                    'message' => 'User Deleted Successfully',
                    "status" => 200
                ];
            }
        } catch (\Exception $ex) {
            return  [
                'message' => $ex->getMessage(),
                "status" => $ex->getCode()
            ];
        }
    }
    private function deleteRelatedDataForOwner()
    {
        $ownerid = Auth::user()->id;
        $shops = Shop::where('ownerId', $ownerid)->get();
        foreach ($shops as $shop) {
            $this->productservice->deleteProductsForSpecificShop($shop);
            $this->shopservice->deleteShop($shop);
        }
    }
    public function restore(LoginOwnerRequest $request)
    {
        //& Find the user by email, including soft-deleted users
        $user = User::withTrashed()->where('email', $request->email)->first();
        $shops = $this->shopservice->restoreShops($user->id);
        $this->productservice->restoreProducts($shops);
        //^ Restore the user if they are soft-deleted and the password is correct
        $userpassword = Hash::check($request->password, $user->password);
        if ($user->trashed() && $userpassword) {
            $user->restore();
            $user->save();
            return  ['message' => 'Account restore successfully.', "status" => 200];
        }
        if (!$user->trashed()) {
            return  ['message' => 'Your Account was restored successfully.', "status" => 200];
        }
        return  ['message' => 'Password incorrect', "status" => 401];
    }


    public function logoutOwner()
    {
        try {
            Auth::logout(); // Optional: Laravel default logout
            JWTAuth::invalidate(JWTAuth::getToken());
            return  ['message' => 'User logged out successfully', 'status' => 200];
        } catch (\Exception $ex) {
            return ['message' => $ex->getMessage(), 'status' => $ex->getCode()];
        }
    }
}
