<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'sport_category',
        'location',
        'city',
        'province',
        'contact_email',
        'contact_phone',
        'instagram',
        'website',
        'member_limit',
        'followers_count',
        'members_count',
        'average_rating',
        'ratings_count',
        'verification_status',
        'verification_type',
        'verification_fee',
        'verification_payment_method',
        'verification_requested_at',
        'rejection_reason',
        'verified_at',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'verified_at' => 'datetime',
        'verification_requested_at' => 'datetime',
        'verification_fee' => 'decimal:2',
        'average_rating' => 'float',
    ];

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function canCreatePaidEvents(): bool
    {
        return $this->isVerified();
    }

    public function canClaimFreeVerification(): bool
    {
        return ($this->followers_count ?? 0) >= 15;
    }

    public function followersNeededForVerification(): int
    {
        return max(0, 15 - (int) ($this->followers_count ?? 0));
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'community_followers');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}