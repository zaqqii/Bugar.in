<?php

namespace App\Services;

use App\Models\User;

class UserLevelService
{
    /**
     * Menghitung level pengguna murni dari users.total_points (akumulatif).
     * Level 1-5 berdasarkan config/points.php:
     * - Level 1: 0-199 (Pemula)
     * - Level 2: 200-499 (Aktif)
     * - Level 3: 500-999 (Konsisten)
     * - Level 4: 1000-1999 (Berkembang)
     * - Level 5: 2000+ (All-Rounder, maksimum)
     *
     * Skor atau bonus streak TIDAK masuk perhitungan level dan TIDAK menambah saldo.
     * Seluruh kunci array yang dibaca view dipertahankan dengan tipe kompatibel.
     *
     * @param User $user
     * @return array
     */
    public function progress(User $user): array
    {
        $totalPoints = max(0, (int) ($user->total_points ?? 0));

        $levelsConfig = config('points.levels', [
            1 => ['name' => 'Pemula', 'min_points' => 0, 'max_points' => 199],
            2 => ['name' => 'Aktif', 'min_points' => 200, 'max_points' => 499],
            3 => ['name' => 'Konsisten', 'min_points' => 500, 'max_points' => 999],
            4 => ['name' => 'Berkembang', 'min_points' => 1000, 'max_points' => 1999],
            5 => ['name' => 'All-Rounder', 'min_points' => 2000, 'max_points' => null],
        ]);

        $maxConfigLevel = count($levelsConfig);
        $currentLevel = 1;

        // Tentukan level dari konfigurasi (evaluasi dari level tertinggi ke level 1)
        for ($lvl = $maxConfigLevel; $lvl >= 1; $lvl--) {
            if (isset($levelsConfig[$lvl]) && $totalPoints >= $levelsConfig[$lvl]['min_points']) {
                $currentLevel = $lvl;
                break;
            }
        }

        $currentConfig = $levelsConfig[$currentLevel] ?? [
            'name' => 'Pemula',
            'min_points' => 0,
            'max_points' => 199,
        ];

        $name = $currentConfig['name'];
        $minPoints = (int) $currentConfig['min_points'];
        $maxPoints = $currentConfig['max_points'] !== null ? (int) $currentConfig['max_points'] : null;

        $isMax = ($maxPoints === null || $currentLevel >= $maxConfigLevel);
        $nextLevel = $isMax ? null : ($currentLevel + 1);

        if ($isMax) {
            $progressPoints = $totalPoints - $minPoints;
            $pointsToNext = 0;
            $progressPercentage = 100;
        } else {
            $nextMin = isset($levelsConfig[$nextLevel])
                ? (int) $levelsConfig[$nextLevel]['min_points']
                : ($maxPoints + 1);
            $pointsToNext = max(0, $nextMin - $totalPoints);
            $levelSpan = max(1, $nextMin - $minPoints);
            $progressPoints = max(0, $totalPoints - $minPoints);
            $progressPercentage = min(100, (int) floor(($progressPoints / $levelSpan) * 100));
        }

        // Longest streak untuk view (tanpa kontribusi ke skor level)
        $longestStreak = max(0, (int) $user->streaks()->max('longest_streak'));

        return [
            // Kunci spesifikasi RF-07a
            'level'                        => $currentLevel,
            'name'                         => $name,
            'nama'                         => $name,
            'min_points'                   => $minPoints,
            'batas_bawah'                  => $minPoints,
            'next_level'                   => $nextLevel,
            'level_berikutnya'             => $nextLevel,
            'points_to_next'               => $pointsToNext,
            'poin_menuju_level_berikutnya' => $pointsToNext,
            'progress_percentage'          => $progressPercentage,
            'persen_progres'               => $progressPercentage,
            'is_max'                       => $isMax,

            // Kunci kompatibilitas yang saat ini dibaca view (dashboard & edit_profile)
            'total_points'                 => $totalPoints,
            'score'                        => $totalPoints, // Skor murni dari total_points (tanpa bonus streak)
            'streak_score'                 => 0,            // Bonus streak tidak menambah saldo/skor level
            'longest_streak'               => $longestStreak,
            'progress_points'              => $progressPoints,
            'points_to_next_level'         => $pointsToNext,
        ];
    }
}
