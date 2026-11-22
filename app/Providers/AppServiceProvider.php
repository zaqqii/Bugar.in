<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('apple', AppleProvider::class);
        });

        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        $buildRateLimitResponse = function (Request $request, array $headers) {
            $message = 'Terlalu banyak percobaan, coba lagi sebentar';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 429, $headers);
            }

            return redirect()->back(fallback: $request->url())
                ->withHeaders($headers)
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'login' => $message,
                    'email' => $message,
                    'throttle' => $message,
                ])
                ->with('error', $message);
        };

        // 1. Rate limiter 'login':
        //    (1) 5 per menit dengan kunci strtolower(email/username)|ip
        //    (2) 60 per menit dengan kunci IP (mencegah password spraying pada jaringan bersama)
        RateLimiter::for('login', function (Request $request) use ($buildRateLimitResponse) {
            $loginIdentifier = strtolower(trim((string) (
                $request->input('login')
                ?? $request->input('email')
                ?? $request->input('username')
                ?? ''
            )));

            return [
                Limit::perMinute(5)
                    ->by($loginIdentifier . '|' . $request->ip())
                    ->response($buildRateLimitResponse),
                Limit::perMinute(60)
                    ->by($request->ip())
                    ->response($buildRateLimitResponse),
            ];
        });

        // 2. Rate limiter 'register': 20 per menit per IP
        RateLimiter::for('register', function (Request $request) use ($buildRateLimitResponse) {
            return Limit::perMinute(20)
                ->by($request->ip())
                ->response($buildRateLimitResponse);
        });

        // 3. Rate limiter 'join': 20 per menit per user ID (fallback IP)
        RateLimiter::for('join', function (Request $request) use ($buildRateLimitResponse) {
            $key = (string) ($request->user()?->id ?: $request->ip());

            return Limit::perMinute(20)
                ->by($key)
                ->response($buildRateLimitResponse);
        });
    }
}

