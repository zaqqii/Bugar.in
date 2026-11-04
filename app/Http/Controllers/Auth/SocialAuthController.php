<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class SocialAuthController extends Controller
{
    private const AUDIENCES = [
        'user' => [
            'role' => 'user',
            'login' => 'auth.user.login',
            'dashboard' => 'user.dashboard',
            'label' => 'pengguna',
        ],
        'penyedia-komunitas' => [
            'role' => 'community_owner',
            'login' => 'auth.penyedia-komunitas.login',
            'dashboard' => 'penyedia-komunitas.dashboard',
            'label' => 'penyedia komunitas',
        ],
    ];

    public function redirect(string $audience, string $provider): RedirectResponse
    {
        abort_unless(isset(self::AUDIENCES[$audience]) && in_array($provider, ['google', 'apple'], true), 404);

        if (!$this->isConfigured($provider)) {
            return redirect()->route(self::AUDIENCES[$audience]['login'])
                ->withErrors(['social' => 'Login ' . ucfirst($provider) . ' belum dikonfigurasi. Lengkapi kredensial OAuth di file environment.']);
        }

        $driver = Socialite::driver($provider);

        if ($provider === 'apple') {
            $response = $driver->stateless()->cookieNonce()->scopes(['name', 'email'])->redirect();

            return $response->withCookie(cookie('social_auth_audience', $audience, 10, '/', null, true, true, false, 'none'));
        }

        session()->put('social_auth_audience', $audience);

        return $driver->scopes(['openid', 'email', 'profile'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    private function isConfigured(string $provider): bool
    {
        $config = config('services.' . $provider, []);

        if ($provider === 'google') {
            return filled($config['client_id'] ?? null)
                && filled($config['client_secret'] ?? null)
                && filled($config['redirect'] ?? null);
        }

        $hasClientSecret = filled($config['client_secret'] ?? null);
        $privateKey = $config['private_key'] ?? null;
        $hasSigningKey = filled($config['key_id'] ?? null)
            && filled($config['team_id'] ?? null)
            && filled($privateKey)
            && is_file($privateKey);

        return filled($config['client_id'] ?? null)
            && filled($config['redirect'] ?? null)
            && ($hasClientSecret || $hasSigningKey);
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        $audience = (string) $request->session()->pull('social_auth_audience', '');

        return $this->handleCallback($request, $audience, 'google');
    }

    public function appleCallback(Request $request): RedirectResponse
    {
        $audience = (string) $request->cookie('social_auth_audience', '');

        return $this->handleCallback($request, $audience, 'apple');
    }

    private function handleCallback(Request $request, string $audience, string $provider): RedirectResponse
    {
        if (!isset(self::AUDIENCES[$audience])) {
            return redirect()->route('auth.selection')
                ->withErrors(['social' => 'Sesi login sosial tidak valid atau sudah kedaluwarsa. Silakan mulai lagi.']);
        }

        $destination = self::AUDIENCES[$audience];

        if ($request->filled('error')) {
            return redirect()->route($destination['login'])
                ->withErrors(['social' => 'Login ' . ucfirst($provider) . ' dibatalkan.']);
        }

        try {
            $identity = $provider === 'apple'
                ? Socialite::driver('apple')->stateless()->cookieNonce()->user()
                : Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            Log::notice('Social login rejected an invalid OAuth state or nonce.', [
                'provider' => $provider,
                'audience' => $audience,
            ]);

            return redirect()->route($destination['login'])
                ->withErrors(['social' => 'Sesi login ' . ucfirst($provider) . ' tidak valid atau sudah kedaluwarsa. Silakan coba lagi.']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route($destination['login'])
                ->withErrors(['social' => 'Login ' . ucfirst($provider) . ' belum dapat diselesaikan. Silakan coba lagi.']);
        }

        $providerUserId = (string) $identity->getId();

        if ($providerUserId === '') {
            return redirect()->route($destination['login'])
                ->withErrors(['social' => 'Identitas akun ' . ucfirst($provider) . ' tidak dapat diverifikasi.']);
        }

        $account = UserSocialAccount::with('user')
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        $user = $account?->user;

        if (!$user) {
            $rawIdentity = $identity->getRaw();
            $emailVerified = filter_var(
                $rawIdentity['email_verified'] ?? $rawIdentity['verified_email'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
            $email = strtolower(trim((string) $identity->getEmail()));

            if (!$emailVerified || $email === '') {
                return redirect()->route($destination['login'])
                    ->withErrors(['social' => 'Email dari akun ' . ucfirst($provider) . ' belum terverifikasi.']);
            }

            $user = User::where('email', $email)
                ->where('role', $destination['role'])
                ->where('is_active', true)
                ->first();

            if (!$user) {
                return redirect()->route($destination['login'])
                    ->withErrors(['social' => 'Belum ada akun ' . $destination['label'] . ' aktif dengan email terverifikasi tersebut. Daftar atau gunakan email yang terhubung ke akun Bugar.in kamu.']);
            }

            try {
                DB::transaction(function () use ($user, $provider, $providerUserId, $email, &$account): void {
                    $existingProviderLink = UserSocialAccount::where('user_id', $user->id)
                        ->where('provider', $provider)
                        ->first();

                    if ($existingProviderLink && $existingProviderLink->provider_user_id !== $providerUserId) {
                        return;
                    }

                    $account = UserSocialAccount::firstOrCreate(
                        ['provider' => $provider, 'provider_user_id' => $providerUserId],
                        ['user_id' => $user->id, 'email' => $email]
                    );
                });
            } catch (Throwable $exception) {
                report($exception);

                return redirect()->route($destination['login'])
                    ->withErrors(['social' => 'Akun ' . ucfirst($provider) . ' tidak dapat ditautkan. Hubungi dukungan jika masalah berlanjut.']);
            }

            if (!$account || $account->user_id !== $user->id) {
                return redirect()->route($destination['login'])
                    ->withErrors(['social' => 'Akun ' . ucfirst($provider) . ' lain sudah ditautkan. Gunakan akun yang sebelumnya terhubung.']);
            }
        }

        if ($user->role !== $destination['role'] || !$user->is_active) {
            return redirect()->route($destination['login'])
                ->withErrors(['social' => 'Akun ini tidak memiliki akses ke halaman login tersebut.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($destination['dashboard']))
            ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
    }
}
