<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
        ]);

        // Only append to web group so it runs after authentication
        $middleware->web(\App\Http\Middleware\UpdateLastActiveAt::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
