<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetricsController;
use App\Http\Middleware\CatatMetrik;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/health', HealthController::class);
            Route::get('/metrics', MetricsController::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(CatatMetrik::class);

        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();