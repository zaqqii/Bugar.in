<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'qris_image',
        'type',
        'location',
        'maps_url',
        'event_date',
        'start_time',
        'end_time',
        'capacity',
        'registration_fee',
        'requirements',
        'prizes',
        'registration_open_at',
        'registration_close_at',
        'status',
        'participants_count',
        'points_reward',
        'is_active',
        'sport_category',
    'rundown',
    'timezone',
    'payment_category',
    'payment_method',
    'payment_account',
    'organizer_contact',
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
        'is_active' => 'boolean',
        'registration_fee' => 'float',
    ];

    protected $appends = ['cover_url', 'qris_url'];

private function mediaUrl(?string $p): ?string
{
    if (!$p) return null;
    return str_starts_with($p, 'http') ? $p : url('media/' . $p);
}

public function getCoverUrlAttribute() { return $this->mediaUrl($this->cover_image); }
public function getQrisUrlAttribute()  { return $this->mediaUrl($this->qris_image); }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function participants()
    {
        return $this->morphMany(Participant::class, 'participatable');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'registration_closed', 'ongoing']);
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->participants_count >= $this->capacity;
    }

    public function fillPercentage(): int
    {
        if (!$this->capacity || $this->capacity === 0) {
            return 0;
        }
        return (int) min(100, round(($this->participants_count / $this->capacity) * 100));
    }
}