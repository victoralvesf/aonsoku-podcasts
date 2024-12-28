<?php

use App\Http\Middleware\EnsureUserAndTenantExist;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(EnsureUserAndTenantExist::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => 'Not Found',
                'status' => 404,
            ], 404);
        });

        $exceptions->renderable(function (Throwable $e, $request) {
            return response()->json([
                'message' => 'Internal Error',
                'status' => 500,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        });
    })->create();
