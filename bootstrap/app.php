<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.user' => \App\Http\Middleware\CheckUserAuthentication::class,
            'auth.admin' => \App\Http\Middleware\CheckAdminAuthentication::class,
            'auth.owner.has.shopById' => \App\Http\Middleware\CheckUserHasThisShop::class,
            'auth.owner.has.shopByName' => \App\Http\Middleware\CheckShopOwnerByStoreName::class,
            'auth.client' => \App\Http\Middleware\CheckClientAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
