<?php

namespace App\Http\Middleware;


use Closure;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CheckUserHasThisShop
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $message = $this->validaterequest($request);
        if (!$message['status']) {
            return response()->json([
                'message' =>  $message['message']
            ]);
        }

        $CheckUserHasThisShopById = Shop::withTrashed()->where('ownerId', Auth::id())->where('shopId', '=', $request->shopId)->first();
        if ($CheckUserHasThisShopById) {
            return $next($request);
        }

        return response()->json([

            'message' =>  $message['message']
        ]);
    }
    private function validaterequest($request)
    {
        $validatorid = Validator::make(
            $request->all(),
            [
                'shopId' => 'required|integer|exists:shop,shopId',
            ]
        );
        if ($validatorid->fails()) {
            return   [

                'message' => $validatorid->errors(),
                'status' => 0
            ];
        }
        return   [

            'message' => 'This shop does not For You',
            'status' => 1
        ];
    }
}
