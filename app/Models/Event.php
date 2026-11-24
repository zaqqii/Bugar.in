<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function activeParticipantsCount(): int
    {
        return $this->participants()->whereIn('status', ['registered', 'waiting_payment'])->count();
    }

    public function endsAt(): ?Carbon
    {
        if (! $this->event_date || ! $this->start_time) {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $dateStr = $this->event_date instanceof Carbon ? $this->event_date->format('Y-m-d') : (string) $this->event_date;
        $timeStr = $this->end_time ?: '23:59:59';

        return Carbon::parse("{$dateStr} {$timeStr}", $tz);
    }

    public function isFinished(): bool
    {
        $endsAt = $this->endsAt();
        if (! $endsAt) {
            return false;
        }

        return Carbon::now(config('app.timezone', 'Asia/Jakarta'))->gt($endsAt);
    }

    public function scopeNotFinished($query)
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        return $query->where(function ($q) use ($today, $currentTime) {
            $q->whereNull('event_date')
              ->orWhereNull('start_time')
              ->orWhereDate('event_date', '>', $today)
              ->orWhere(function ($sub) use ($today, $currentTime) {
                  $sub->whereDate('event_date', $today)
                      ->where(function ($timeSub) use ($currentTime) {
                          $timeSub->whereNull('end_time')
                                  ->orWhere('end_time', '>=', $currentTime);
                      });
              });
        });
    }

    public function canBeEdited(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled'], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'published', 'registration_closed'], true);
    }

    public function canBePublished(): bool
    {
        return $this->status === 'draft';
    }

    public function canCloseRegistration(): bool
    {
        return $this->status === 'published';
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft' || $this->participants()->count() === 0;
    }
}
