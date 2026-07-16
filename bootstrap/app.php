<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check_role' => CheckRole::class
        ]);

        // Logout tidak perlu dicek CSRF: token yang kedaluwarsa (sesi lama/tab lama)
        // membuat logout gagal dengan 419. Melewatkan CSRF di sini aman karena
        // efek terburuk hanyalah user ter-logout, dan itu memang tujuannya.
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ganti halaman gelap "419 PAGE EXPIRED" dengan pengalihan yang ramah:
        // bila token CSRF kedaluwarsa (sesi berakhir), arahkan ke halaman login
        // beserta pesan yang jelas, alih-alih menampilkan error mentah.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
                ], 419);
            }

            return redirect('/login')
                ->with('failed', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();
