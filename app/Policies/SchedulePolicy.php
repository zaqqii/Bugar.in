<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Tentukan apakah user adalah pemilik komunitas dari jadwal ini.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function toggleActive(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function toggleStreak(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function cancel(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function cancelSeries(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function removeParticipant(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function blockParticipant(User $user, Schedule $schedule): bool
    {
        return $this->isOwner($user, $schedule);
    }

    public function isOwner(User $user, Schedule $schedule): bool
    {
        return $schedule->community !== null && (int) $schedule->community->owner_id === (int) $user->id;
    }
}
