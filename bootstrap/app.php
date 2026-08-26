<?php

use App\Http\Middleware\CheckRole;
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
        $middleware->statefulApi();

        // Di Render (dan PaaS lain) TLS diputus di load balancer, lalu request
        // diteruskan ke container lewat HTTP biasa dengan header
        // X-Forwarded-Proto: https. Tanpa mempercayai proxy, Laravel mengira
        // koneksinya http:// sehingga URL yang dibentuk salah skema dan cookie
        // sesi bertanda secure tidak pernah terkirim balik -- login dan CSRF
        // ikut rusak. IP load balancer-nya tidak tetap, jadi dipercaya semua.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
