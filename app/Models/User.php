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
        'google_id',
        'password',
        'phone',
        'role',
        'avatar',
        'bio',
        'sports',
        'fitness_level',
        'weekly_target',
        'sparring_open',
        'date_of_birth',
        'gender',
        'height_cm',
        'weight_kg',
        'total_points',
        'is_active',
        'join_blocked_until',
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
            'sports' => 'array',
            'sparring_open' => 'boolean',
            'join_blocked_until' => 'datetime',
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

    public function userAnnouncements()
    {
        return $this->hasMany(UserAnnouncement::class);
    }

    public function streaks()
    {
        return $this->hasMany(Streak::class);
    }

    public function pointsHistories()
    {
        return $this->hasMany(PointsHistory::class);
    }

    public function communityBlocks()
    {
        return $this->hasMany(CommunityBlock::class);
    }

    public function isJoinBlocked(): bool
    {
        return $this->join_blocked_until !== null && $this->join_blocked_until->isFuture();
    }

    public function isCommunityOwner(): bool
    {
        return $this->role === 'community_owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Daftar cabang olahraga lengkap yang didukung oleh Bugar.in.
     * Digunakan untuk onboarding setelah registrasi, edit profil, dan filter komunitas.
     */
    public static function availableSports(): array
    {
        return [
            ['id' => 'lari', 'label' => 'Lari / Running', 'icon' => '🏃', 'category' => 'Cardio', 'aliases' => ['running']],
            ['id' => 'sepeda', 'label' => 'Sepeda / Cycling', 'icon' => '🚴', 'category' => 'Cardio', 'aliases' => ['cycling']],
            ['id' => 'renang', 'label' => 'Renang / Swimming', 'icon' => '🏊', 'category' => 'Cardio', 'aliases' => ['swimming']],
            ['id' => 'badminton', 'label' => 'Badminton / Bulu Tangkis', 'icon' => '🏸', 'category' => 'Raket', 'aliases' => []],
            ['id' => 'padel', 'label' => 'Padel Tennis', 'icon' => '🎾', 'category' => 'Raket', 'aliases' => []],
            ['id' => 'tenis', 'label' => 'Tenis Lapangan', 'icon' => '🎾', 'category' => 'Raket', 'aliases' => ['tennis']],
            ['id' => 'tenis_meja', 'label' => 'Tenis Meja / Pingpong', 'icon' => '🏓', 'category' => 'Raket', 'aliases' => ['table_tennis']],
            ['id' => 'basket', 'label' => 'Basket / Basketball', 'icon' => '🏀', 'category' => 'Tim', 'aliases' => ['basketball']],
            ['id' => 'futsal', 'label' => 'Futsal / Mini Soccer', 'icon' => '⚽', 'category' => 'Tim', 'aliases' => []],
            ['id' => 'sepakbola', 'label' => 'Sepak Bola / Football', 'icon' => '⚽', 'category' => 'Tim', 'aliases' => ['football', 'soccer']],
            ['id' => 'voli', 'label' => 'Voli / Volleyball', 'icon' => '🏐', 'category' => 'Tim', 'aliases' => ['volleyball']],
            ['id' => 'gym', 'label' => 'Gym & Fitness', 'icon' => '🏋️', 'category' => 'Strength & Fitness', 'aliases' => ['strength', 'fitness']],
            ['id' => 'kalistenik', 'label' => 'Calisthenics / Bodyweight', 'icon' => '💪', 'category' => 'Strength & Fitness', 'aliases' => ['calisthenics']],
            ['id' => 'senam', 'label' => 'Senam / Aerobik / Zumba', 'icon' => '🤸', 'category' => 'Strength & Fitness', 'aliases' => ['aerobik', 'zumba']],
            ['id' => 'yoga', 'label' => 'Yoga & Pilates', 'icon' => '🧘', 'category' => 'Mind & Body', 'aliases' => ['pilates']],
            ['id' => 'hiking', 'label' => 'Hiking & Trail Run', 'icon' => '🧗', 'category' => 'Outdoor & Combat', 'aliases' => ['trail', 'outdoor']],
            ['id' => 'bela_diri', 'label' => 'Bela Diri / Martial Arts', 'icon' => '🥋', 'category' => 'Outdoor & Combat', 'aliases' => ['martial', 'martial_arts']],
            ['id' => 'boxing', 'label' => 'Boxing & Muay Thai', 'icon' => '🥊', 'category' => 'Outdoor & Combat', 'aliases' => ['kickboxing']],
            ['id' => 'golf', 'label' => 'Golf', 'icon' => '⛳', 'category' => 'Akurasi & Lainnya', 'aliases' => []],
            ['id' => 'panahan', 'label' => 'Panahan / Archery', 'icon' => '🏹', 'category' => 'Akurasi & Lainnya', 'aliases' => ['archery']],
            ['id' => 'bowling', 'label' => 'Bowling', 'icon' => '🎳', 'category' => 'Akurasi & Lainnya', 'aliases' => []],
            ['id' => 'skate', 'label' => 'Skateboard & Roller Skate', 'icon' => '🛹', 'category' => 'Akurasi & Lainnya', 'aliases' => ['skateboard']],
            ['id' => 'other', 'label' => 'Olahraga Lainnya', 'icon' => '🎯', 'category' => 'Akurasi & Lainnya', 'aliases' => []],
        ];
    }
}
