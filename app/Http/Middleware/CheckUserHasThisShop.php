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


        $validatorid = Validator::make(
            $request->all(),
            [
                'shopId' => 'required |exists:shop,shopId',
            ]

        );
        $CheckUserHasThisShopById = shop::where('ownerId', Auth::id())->where('shopId', '=', $request->shopId)->first();

        $CheckUserHasThisShop = Shop::where('ownerId', Auth::id())->where('nameOfStore', '=', $request->nameOfStore)->first();
        if ($CheckUserHasThisShopById) {
            if ($validatorid->fails()) {
                return response()->json([

                    'message' => $validatorid->errors()
                ]);
            }
            // return $CheckUserHasThisShop;
            return $next($request);
        }



        if ($CheckUserHasThisShop) {
            return $next($request);
        }


        return response()->json([

            'message' => 'This shop does not For You'
        ]);
    }
}
