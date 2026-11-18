<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai header reverse proxy (Nginx/DomCloud) agar skema HTTPS & URL route akurat
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/');

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            if ($response->getStatusCode() !== 419) {
                return $response;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi halaman telah kedaluwarsa. Silakan muat ulang halaman.',
                ], 419);
            }

            if ($request->is('logout')) {
                if (! Auth::check()) {
                    if ($request->hasSession()) {
                        $request->session()->regenerateToken();
                    }

                    return redirect()->route('login')
                        ->with('success', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
                }

                $origin = $request->headers->get('origin');
                $referer = $request->headers->get('referer');
                $originHost = $origin ? parse_url($origin, PHP_URL_HOST) : null;
                $refererHost = $referer ? parse_url($referer, PHP_URL_HOST) : null;
                $currentHost = $request->getHost();

                $isSameOrigin = ($originHost !== null && $originHost === $currentHost)
                    || ($refererHost !== null && $refererHost === $currentHost);

                if ($isSameOrigin) {
                    Auth::logout();
                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    }

                    return redirect()->route('login')
                        ->with('success', 'Anda telah berhasil keluar.');
                }

                return redirect('/')
                    ->with('error', 'Token sesi halaman kedaluwarsa. Silakan klik Logout kembali.');
            }

            if ($request->is('login')) {
                return redirect()->route('login')
                    ->withInput($request->only('username'))
                    ->withErrors([
                        'username' => 'Sesi halaman login kedaluwarsa. Silakan coba masuk kembali.',
                    ]);
            }

            return back()
                ->withInput($request->except(
                    'password',
                    'password_confirmation',
                    'current_password',
                    'new_password',
                    'new_password_confirmation',
                    '_token'
                ))
                ->with('error', 'Sesi halaman telah kedaluwarsa. Silakan ulangi kembali.');
        });
    })->create();
