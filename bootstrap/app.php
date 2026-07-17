<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'patient.active' => \App\Http\Middleware\EnsurePatientIsActive::class,
        ]);

        // لا يوجد صفحة login في الـ API — الطلبات غير المصادَق عليها ترجع 401 JSON
        $middleware->redirectGuestsTo(fn ($request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // راوتات الـ API ترجع JSON دائماً (401 بدل redirect لصفحة login غير موجودة)
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*') || $request->expectsJson());
    })->create();
