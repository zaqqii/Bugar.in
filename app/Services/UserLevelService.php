<?php

namespace App\Services;

use App\Models\User;

class UserLevelService
{
    private const POINTS_PER_LEVEL = 300;

    private const STREAK_SCORE_PER_WEEK = 20;

    private const STREAK_DAYS_PER_WEEK = 7;

    /**
     * Calculate the user's level from spendable Gerak Poin and their best
     * recorded streak. Streak score is progression only; it is not a balance.
     *
     * @return array{level:int,score:int,total_points:int,longest_streak:int,streak_score:int,progress_points:int,points_to_next_level:int,progress_percentage:int}
     */
    public function progress(User $user): array
    {
        $totalPoints = max(0, (int) ($user->total_points ?? 0));
        $longestStreak = max(0, (int) $user->streaks()->max('longest_streak'));
        $streakScore = intdiv($longestStreak, self::STREAK_DAYS_PER_WEEK) * self::STREAK_SCORE_PER_WEEK;
        $score = $totalPoints + $streakScore;
        $level = intdiv($score, self::POINTS_PER_LEVEL) + 1;
        $progressPoints = $score % self::POINTS_PER_LEVEL;

        return [
            'level' => $level,
            'score' => $score,
            'total_points' => $totalPoints,
            'longest_streak' => $longestStreak,
            'streak_score' => $streakScore,
            'progress_points' => $progressPoints,
            'points_to_next_level' => self::POINTS_PER_LEVEL - $progressPoints,
            'progress_percentage' => (int) floor(($progressPoints / self::POINTS_PER_LEVEL) * 100),
        ];
    }
}
