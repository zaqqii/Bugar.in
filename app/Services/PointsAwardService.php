<?php

namespace App\Services;

use App\Models\PointsHistory;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PointsAwardService
{
    private const SOURCE = 'other';
    private const DAILY_LOGIN_POINTS = 2;

    public function awardDailyLogin(User $user): int
    {
        if ($user->role !== 'user') {
            return 0;
        }

        return DB::transaction(function () use ($user): int {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $dayStart = CarbonImmutable::now('Asia/Jakarta')
                ->startOfDay()
                ->setTimezone(config('app.timezone', 'UTC'));
            $nextDay = $dayStart->addDay();

            $alreadyAwarded = PointsHistory::query()
                ->where('user_id', $lockedUser->id)
                ->where('source', self::SOURCE)
                ->where('pointable_type', $lockedUser->getMorphClass())
                ->where('pointable_id', $lockedUser->getKey())
                ->where('description', 'Login harian')
                ->where('created_at', '>=', $dayStart)
                ->where('created_at', '<', $nextDay)
                ->exists();

            if ($alreadyAwarded) {
                return 0;
            }

            $this->record($lockedUser, self::DAILY_LOGIN_POINTS, $lockedUser, 'Login harian');

            return self::DAILY_LOGIN_POINTS;
        });
    }

    public function awardScheduleParticipation(User $user, Schedule $schedule): int
    {
        return DB::transaction(function () use ($user, $schedule): int {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $scheduleType = $schedule->getMorphClass();

            $alreadyAwarded = PointsHistory::query()
                ->where('user_id', $lockedUser->id)
                ->where('source', self::SOURCE)
                ->where('pointable_type', $scheduleType)
                ->where('pointable_id', $schedule->getKey())
                ->where('description', 'like', 'Ikut jadwal komunitas:%')
                ->exists();

            if ($alreadyAwarded) {
                return 0;
            }

            $verificationStatus = $schedule->community()
                ->lockForUpdate()
                ->value('verification_status');
            $points = $verificationStatus === 'verified' ? 15 : 10;
            $description = 'Ikut jadwal komunitas: ' . $schedule->title;

            $this->record($lockedUser, $points, $schedule, $description);

            return $points;
        });
    }

    private function record(User $user, int $points, Model $pointable, string $description): void
    {
        $balance = (int) $user->total_points + $points;
        $user->total_points = $balance;
        $user->save();

        $history = new PointsHistory([
            'user_id' => $user->id,
            'points' => $points,
            'source' => self::SOURCE,
            'description' => $description,
            'balance_after' => $balance,
        ]);
        $history->pointable()->associate($pointable);
        $history->save();
    }
}
