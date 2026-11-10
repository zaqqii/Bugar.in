<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Support\Facades\Cache;

class CheckinTokenService
{
    /**
     * Buat token check-in acak baru dan simpan payload sesi di Cache.
     * Token TIDAK memuat user_id atau data sensitif apapun.
     *
     * @return array{token: string, expires_in: int, rotate_in: int}
     */
    public function issue(Participant $participant): array
    {
        $token = bin2hex(random_bytes(20));
        $cacheKey = $this->cacheKey($token);
        $ttlSeconds = (int) config('schedules.checkin_token_ttl_seconds', 90);
        $rotateSeconds = (int) config('schedules.checkin_token_rotate_seconds', 45);

        $payload = [
            'user_id' => $participant->user_id,
            'participant_id' => $participant->id,
            'schedule_id' => $participant->participatable_id,
            'created_at' => now()->timestamp,
        ];

        Cache::put($cacheKey, $payload, now()->addSeconds($ttlSeconds));

        return [
            'token' => $token,
            'expires_in' => $ttlSeconds,
            'rotate_in' => $rotateSeconds,
        ];
    }

    /**
     * Ambil data sesi terkait token dari Cache jika masih valid.
     *
     * @return array{user_id: int, participant_id: int, schedule_id: int, created_at: int}|null
     */
    public function resolve(string $token): ?array
    {
        if (empty($token) || strlen($token) !== 40) {
            return null;
        }

        $cacheKey = $this->cacheKey($token);
        return Cache::get($cacheKey);
    }

    /**
     * Hapus token dari Cache setelah berhasil diproses (sekali pakai).
     */
    public function consume(string $token): void
    {
        if (empty($token)) {
            return;
        }

        $cacheKey = $this->cacheKey($token);
        Cache::forget($cacheKey);
    }

    /**
     * Bangun kunci cache berdasar hash sha256 dari token.
     */
    private function cacheKey(string $token): string
    {
        return 'qrci:' . hash('sha256', $token);
    }
}

