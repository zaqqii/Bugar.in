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

    /**
     * Jumlah kursi terpakai pada event ini.
     * Menggunakan Participant::SEAT_STATUSES (registered, waiting_payment, attended).
     */
    public function seatsTaken(): int
    {
        return $this->participants()->holdingSeat()->count();
    }

    /**
     * Sisa kursi yang tersedia pada event ini.
     * Mengembalikan null jika kapasitas tanpa batas (capacity === null).
     */
    public function remainingSeats(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->seatsTaken());
    }

    /**
     * Menentukan apakah event sudah penuh.
     * Kapasitas null = tanpa batas (tidak pernah penuh).
     */
    public function isFull(): bool
    {
        if ($this->capacity === null) {
            return false;
        }

        return $this->seatsTaken() >= $this->capacity;
    }

    public function fillPercentage(): int
    {
        if (!$this->capacity || $this->capacity === 0) {
            return 0;
        }
        return (int) min(100, round(($this->seatsTaken() / $this->capacity) * 100));
    }

    public function activeParticipantsCount(): int
    {
        return $this->seatsTaken();
    }

    /**
     * Sinkronkan counter participants_count langsung dari jumlah riil kursi terpakai.
     * Mengoreksi deviasi data secara otomatis tanpa bergantung pada increment/decrement.
     */
    public function syncParticipantsCount(): int
    {
        $count = $this->seatsTaken();
        $this->participants_count = $count;
        $this->save();

        return $count;
    }

    public function timezoneName(): string
    {
        $tz = strtoupper(trim((string) ($this->timezone ?: 'WIB')));

        return match ($tz) {
            'WITA' => 'Asia/Makassar',
            'WIT'  => 'Asia/Jayapura',
            'WIB'  => 'Asia/Jakarta',
            default => in_array($this->timezone, timezone_identifiers_list(), true)
                ? $this->timezone
                : config('app.timezone', 'Asia/Jakarta'),
        };
    }

    public function timezoneLabel(): string
    {
        $tz = strtoupper(trim((string) ($this->timezone ?: 'WIB')));

        return match ($tz) {
            'WITA' => 'WITA',
            'WIT'  => 'WIT',
            default => 'WIB',
        };
    }

    public function startsAt(): ?Carbon
    {
        if (! $this->event_date || ! $this->start_time) {
            return null;
        }

        $tz = $this->timezoneName();
        $dateStr = $this->event_date instanceof Carbon ? $this->event_date->format('Y-m-d') : (string) $this->event_date;
        $timeStr = $this->start_time;

        return Carbon::parse("{$dateStr} {$timeStr}", $tz);
    }

    public function overlapEndsAt(): ?Carbon
    {
        if (! $this->event_date || ! $this->start_time) {
            return null;
        }

        $tz = $this->timezoneName();
        $dateStr = $this->event_date instanceof Carbon ? $this->event_date->format('Y-m-d') : (string) $this->event_date;

        if ($this->end_time) {
            return Carbon::parse("{$dateStr} {$this->end_time}", $tz);
        }

        $defaultMinutes = (int) config('schedules.default_event_duration_minutes', 120);

        return $this->startsAt()->copy()->addMinutes($defaultMinutes);
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
