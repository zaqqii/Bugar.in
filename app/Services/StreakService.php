<?php

namespace App\Services;

use App\Models\Streak;
use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    /**
     * Catat absensi kehadiran untuk streak mingguan pengguna.
     * Menggunakan zona waktu config('app.timezone') (Asia/Jakarta),
     * dengan siklus pekan Senin sampai Minggu (ISO-8601).
     *
     * Aturan:
     * - Tidak ada baris streak atau last_activity_date > minggu lalu -> current_streak = 1, streak_started_at = hari ini.
     * - last_activity_date di minggu yang sama -> current_streak tidak berubah.
     * - last_activity_date di minggu lalu -> current_streak + 1.
     * - Perbarui longest_streak dan last_activity_date.
     */
    public function recordWeeklyAttendance(User $user, ?string $sportCategory = null): Streak
    {
        $category = !empty(trim((string) $sportCategory)) ? trim((string) $sportCategory) : 'Kebugaran';
        $tz = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();

        $streak = Streak::firstOrCreate(
            [
                'user_id' => $user->id,
                'sport_category' => $category,
            ],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
                'streak_started_at' => $today->toDateString(),
            ]
        );

        $currentWeekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $previousWeekStart = $currentWeekStart->copy()->subWeek();

        if (! $streak->last_activity_date) {
            $streak->current_streak = 1;
            $streak->streak_started_at = $today->toDateString();
        } else {
            $lastActivity = Carbon::parse($streak->last_activity_date, $tz)->startOfDay();
            $lastActivityWeekStart = $lastActivity->copy()->startOfWeek(Carbon::MONDAY);

            if ($lastActivityWeekStart->equalTo($currentWeekStart)) {
                // Di minggu yang sama -> current_streak tidak berubah
                if ($streak->current_streak < 1) {
                    $streak->current_streak = 1;
                }
            } elseif ($lastActivityWeekStart->equalTo($previousWeekStart)) {
                // Di minggu lalu -> current_streak bertambah 1
                $streak->current_streak += 1;
            } else {
                // Lebih lama dari minggu lalu -> reset ke 1
                $streak->current_streak = 1;
                $streak->streak_started_at = $today->toDateString();
            }
        }

        $streak->longest_streak = max((int) $streak->longest_streak, (int) $streak->current_streak);
        $streak->last_activity_date = $today->toDateString();
        $streak->save();

        return $streak;
    }
}

