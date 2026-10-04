<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\FetchQuranData::class,
        \App\Console\Commands\WarmDurations::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Progress pings are unauthenticated, rate-limited counters; they are also
        // sent via sendBeacon which cannot carry CSRF headers.
        $middleware->validateCsrfTokens(except: ['api/links/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
