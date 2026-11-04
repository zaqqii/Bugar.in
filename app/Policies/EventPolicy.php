<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Tentukan apakah user adalah pemilik komunitas dari event ini.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->isOwner($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->isOwner($user, $event);
    }

    public function publish(User $user, Event $event): bool
    {
        return $this->isOwner($user, $event);
    }

    public function closeRegistration(User $user, Event $event): bool
    {
        return $this->isOwner($user, $event);
    }

    public function cancel(User $user, Event $event): bool
    {
        return $this->isOwner($user, $event);
    }

    protected function isOwner(User $user, Event $event): bool
    {
        return $event->community !== null && (int) $event->community->owner_id === (int) $user->id;
    }
}

