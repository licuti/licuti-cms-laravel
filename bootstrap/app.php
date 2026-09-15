<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SetLocale;
use App\Providers\RepositoryServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Áp dụng ForceJsonResponse và SetLocale cho toàn bộ API routes
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            SetLocale::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Cấu hình chuyển hướng mặc định khi chưa đăng nhập
        $middleware->redirectTo(
            guests: '/admin/login'
        );
    })
    ->withProviders([
        RepositoryServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
