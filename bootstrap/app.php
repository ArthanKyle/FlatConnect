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
        'staff' => \App\Http\Middleware\StaffMiddleware::class,
        'client' => \App\Http\Middleware\ClientMiddleware::class,
        'client.verified' => \App\Http\Middleware\EnsureClientEmailIsVerified::class,
    ]);
    })
    ->withCommands([
    \App\Console\Commands\BlockOverdueClients::class,
    \App\Console\Commands\ListOverdueClients::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
