<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\PointsAwardService;
use Illuminate\Auth\Events\Login;

class AwardDailyLoginPoints
{
    public function __construct(private readonly PointsAwardService $points)
    {
    }

    public function handle(Login $event): void
    {
        if (! ($event->user instanceof User)) {
            return;
        }

        $awarded = $this->points->awardDailyLogin($event->user);

        if ($awarded > 0) {
            $event->user->refresh();
            session()->flash('daily_login_reward', $awarded);
        }
    }
}
