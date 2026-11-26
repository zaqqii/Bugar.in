<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PointsHistory;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PointsAwardService
{
    /**
     * Memberikan poin login harian (+2) khusus untuk role 'user'.
     * Menggunakan zona Asia/Jakarta dan dedupe_key harian: login:{user_id}:{Y-m-d}.
     *
     * @param User $user
     * @return int Jumlah poin yang diberikan (2 jika berhasil, 0 jika sudah diklaim hari ini / bukan user)
     */
    public function awardDailyLogin(User $user): int
    {
        if ($user->role !== 'user') {
            return 0;
        }

        $points = (int) config('points.daily_login', 2);
        $todayStr = CarbonImmutable::now('Asia/Jakarta')->format('Y-m-d');
        $dedupeKey = "login:{$user->id}:{$todayStr}";

        $history = $this->record(
            user: $user,
            points: $points,
            source: 'other',
            pointable: $user,
            description: 'Login harian',
            dedupeKey: $dedupeKey
        );

        return $history ? $points : 0;
    }

    /**
     * Memberikan poin kehadiran sesi kegiatan (jadwal atau event).
     * - Jadwal: nominal dari config points.schedule_attendance (fallback schedules.attendance_points, default 30).
     * - Event: 10 jika komunitas verified, 5 jika belum verified.
     * Menggunakan dedupe_key: attendance:{participant_id}.
     *
     * @param Participant $participant
     * @return PointsHistory|null
     */
    public function awardAttendance(Participant $participant): ?PointsHistory
    {
        $participant->loadMissing(['participatable', 'user']);
        $activity = $participant->participatable;

        if ($activity instanceof Schedule) {
            $points = (int) config('points.schedule_attendance', config('schedules.attendance_points', 30));
            $description = "Check-in sesi jadwal '{$activity->title}' (+{$points} Poin)";
        } elseif ($activity instanceof Event) {
            $community = $activity->community;
            $isVerified = ($community && $community->verification_status === 'verified');
            $points = $isVerified
                ? (int) config('points.event_attendance_verified', 10)
                : (int) config('points.event_attendance_unverified', 5);
            $description = "Kehadiran event '{$activity->title}' (+{$points} Poin)";
        } else {
            return null;
        }

        $dedupeKey = "attendance:{$participant->id}";

        return $this->record(
            user: $participant->user_id,
            points: $points,
            source: 'attendance',
            pointable: $activity,
            description: $description,
            dedupeKey: $dedupeKey
        );
    }

    /**
     * Membalikkan pemberian poin kehadiran (poin negatif).
     * Saldo total_points pengguna tidak boleh menjadi negatif (minimal 0).
     * Hanya dieksekusi bila poin kehadiran asli ada dan belum dibalik.
     * Menggunakan dedupe_key: attendance-reversal:{participant_id}.
     * Catatan: Metode ini tidak dipasang di mana pun pada task RF-07a.
     *
     * @param Participant $participant
     * @param string $reason
     * @return PointsHistory|null
     */
    public function reverseAttendance(Participant $participant, string $reason): ?PointsHistory
    {
        $attendanceDedupeKey = "attendance:{$participant->id}";
        $reversalDedupeKey = "attendance-reversal:{$participant->id}";

        // 1. Cek apakah poin asli ada
        $original = PointsHistory::where('user_id', $participant->user_id)
            ->where('dedupe_key', $attendanceDedupeKey)
            ->where('points', '>', 0)
            ->first();

        if (! $original) {
            return null;
        }

        $reversalPoints = -abs((int) $original->points);
        $description = "Pembalikan poin kehadiran: {$reason}";

        return $this->record(
            user: $participant->user_id,
            points: $reversalPoints,
            source: 'other',
            pointable: $original->pointable,
            description: $description,
            dedupeKey: $reversalDedupeKey
        );
    }

    /**
     * Metode inti pencatatan poin gerak:
     * 1. Berjalan di dalam DB::transaction.
     * 2. Mengunci baris user terlebih dahulu (lockForUpdate).
     * 3. Memeriksa dedupe_key sebelum menulis.
     * 4. Menulis baris history lengkap dengan dedupe_key menggunakan insertOrIgnore (ON CONFLICT DO NOTHING).
     * 5. Memperbarui users.total_points dan balance_after secara konsisten dari nilai yang dikunci.
     *
     * @param User|int $user
     * @param int $points
     * @param string $source
     * @param Model|null $pointable
     * @param string $description
     * @param string|null $dedupeKey
     * @return PointsHistory|null
     */
    public function record(
        User|int $user,
        int $points,
        string $source,
        ?Model $pointable,
        string $description,
        ?string $dedupeKey = null
    ): ?PointsHistory {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return DB::transaction(function () use ($userId, $points, $source, $pointable, $description, $dedupeKey) {
            // 1. Kunci baris user terlebih dahulu
            /** @var User|null $lockedUser */
            $lockedUser = User::query()->whereKey($userId)->lockForUpdate()->first();
            if (! $lockedUser) {
                return null;
            }

            // 2. Periksa dedupe_key sebelum menulis
            if ($dedupeKey !== null && PointsHistory::where('dedupe_key', $dedupeKey)->exists()) {
                return null;
            }

            // 3. Hitung saldo poin baru secara konsisten dari baris yang dikunci (total_points tidak boleh di bawah 0)
            $currentBalance = (int) ($lockedUser->total_points ?? 0);
            $newBalance = max(0, $currentBalance + $points);

            // 4. Siapkan data baris history
            $now = now();
            $data = [
                'user_id'        => $lockedUser->id,
                'points'         => $points,
                'source'         => $source,
                'pointable_type' => $pointable ? $pointable->getMorphClass() : null,
                'pointable_id'   => $pointable ? $pointable->getKey() : null,
                'description'    => $description,
                'balance_after'  => $newBalance,
                'dedupe_key'     => $dedupeKey,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // 5. Tulis dengan insertOrIgnore (ON CONFLICT DO NOTHING) sebagai pengaman unique di database
            $inserted = DB::table('points_histories')->insertOrIgnore($data);

            if ($inserted === 0) {
                return null;
            }

            // 6. Perbarui users.total_points secara konsisten
            $lockedUser->total_points = $newBalance;
            $lockedUser->save();

            // 7. Ambil dan kembalikan record PointsHistory yang baru dibuat
            if ($dedupeKey !== null) {
                return PointsHistory::where('dedupe_key', $dedupeKey)->first();
            }

            return PointsHistory::where('user_id', $lockedUser->id)
                ->latest('id')
                ->first();
        });
    }
}
