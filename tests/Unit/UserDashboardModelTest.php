<?php

namespace Tests\Unit;

use App\Http\Controllers\User\DashboardController;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserDashboardModelTest extends TestCase
{
    public function test_dashboard_relationships_exist_for_user_model()
    {
        $this->assertTrue(method_exists(User::class, 'communityMemberships'));
        $this->assertTrue(method_exists(User::class, 'participants'));
        $this->assertTrue(method_exists(User::class, 'streaks'));
        $this->assertTrue(method_exists(User::class, 'pointsHistories'));
        $this->assertTrue(method_exists(User::class, 'followedCommunities'));
        $this->assertTrue(method_exists(Community::class, 'followers'));
        $this->assertTrue(class_exists(PointsHistory::class));
        $this->assertTrue(class_exists(Attendance::class));
        $this->assertTrue(method_exists(DashboardController::class, 'followCommunity'));
        $this->assertTrue(method_exists(DashboardController::class, 'unfollowCommunity'));
        $this->assertTrue(method_exists(DashboardController::class, 'joinSchedule'));
        $this->assertTrue(method_exists(DashboardController::class, 'joinEvent'));
        $this->assertTrue(method_exists(DashboardController::class, 'redeemReward'));
        $this->assertTrue(method_exists(DashboardController::class, 'streakCheckIn'));
        $this->assertTrue(Route::has('user.communities.follow'));
        $this->assertTrue(Route::has('user.communities.unfollow'));
        $this->assertTrue(Route::has('user.schedules.join'));
        $this->assertTrue(Route::has('user.events.join'));
        $this->assertTrue(Route::has('user.rewards.redeem'));
        $this->assertTrue(Route::has('user.streak.check-in'));
    }

    public function test_points_history_polymorphic_relation()
    {
        $history = new PointsHistory();
        $this->assertTrue(method_exists($history, 'pointable'));
    }
}
