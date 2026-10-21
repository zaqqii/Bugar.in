<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'bio',
        'total_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedCommunities()
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    public function communityMemberships()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function followedCommunities()
    {
        return $this->belongsToMany(Community::class, 'community_followers');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function streaks()
    {
        return $this->hasMany(Streak::class);
    }

    public function pointsHistories()
    {
        return $this->hasMany(PointsHistory::class);
    }

    public function isCommunityOwner(): bool
    {
        return $this->role === 'community_owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}