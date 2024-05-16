<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {

            if (Auth::check()) {
                // User is authenticated
                return $next($request);
            }

            // User is not authenticated, redirect or return an error response
            return response()->json(['error' => 'Unauthenticated'], 401);
        } catch (\Exception $ex) {
            return response()->json([$ex->getCode(), $ex->getMessage()], 500);
        } catch (JWTException $e) {

            return  response()->json(['1', 'token invalid ' . $e->getMessage()]);
        }
    }
}
