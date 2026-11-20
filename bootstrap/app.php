<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(static fn () => \App\Jobs\CreateUpcomingActivityReminders::dispatch())
            ->dailyAt('08:00')
            ->timezone('Asia/Jakarta');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('auth.user.login'));
        $middleware->validateCsrfTokens(except: [
            'auth/social/apple/callback',
        ]);
        $middleware->alias([
            'community_owner' => \App\Http\Middleware\EnsureCommunityOwner::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            $message = 'Terlalu banyak percobaan, coba lagi sebentar';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 429, $e->getHeaders());
            }

            return redirect()->back(fallback: $request->url())
                ->withHeaders($e->getHeaders())
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'login' => $message,
                    'email' => $message,
                    'throttle' => $message,
                ])
                ->with('error', $message);
        });
    })->create();
