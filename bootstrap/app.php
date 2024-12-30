<?php

use App\Http\Middleware\EnsureUserAndTenantExist;
use App\Http\Middleware\JsonMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            JsonMiddleware::class,
            EnsureUserAndTenantExist::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
