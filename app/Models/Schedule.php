<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Participant;
use App\Models\User;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';
    public const LEVEL_ALL_LEVELS = 'all_levels';

    protected $fillable = [
        'community_id',
        'date',
        'title',
        'level',
        'description',
        'location',
        'maps_url',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'series_id',
        'cancelled_at',
        'is_recurring',
        'is_active',
        'streak_enabled',
    ];

    protected $casts = [
        'date' => 'date',
        'cancelled_at' => 'datetime',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'streak_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Schedule $schedule) {
            if ($schedule->date) {
                $tz = config('app.timezone', 'Asia/Jakarta');
                $dateObj = $schedule->date instanceof Carbon
                    ? $schedule->date
                    : Carbon::parse($schedule->date, $tz);
                $schedule->day_of_week = strtolower($dateObj->englishDayOfWeek);
            }
            $schedule->is_recurring = !empty($schedule->series_id);
        });
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function participants()
    {
        return $this->morphMany(Participant::class, 'participatable');
    }

    public static function levelOptions(): array
    {
        return [
            self::LEVEL_ALL_LEVELS => 'Semua level',
            self::LEVEL_BEGINNER => 'Pemula',
            self::LEVEL_INTERMEDIATE => 'Menengah',
            self::LEVEL_ADVANCED => 'Mahir',
        ];
    }

    public function levelLabel(): string
    {
        return self::levelOptions()[$this->level] ?? 'Semua level';
    }

    public function levelBadgeClass(): string
    {
        return match ($this->level) {
            self::LEVEL_BEGINNER => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::LEVEL_INTERMEDIATE => 'bg-blue-50 text-blue-700 border-blue-200',
            self::LEVEL_ADVANCED => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function dayLabel(): string
    {
        if ($this->date) {
            return $this->startsAt()->locale('id')->isoFormat('dddd');
        }

        $map = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        return $map[$this->day_of_week] ?? ucfirst((string) $this->day_of_week);
    }

    public function startsAt(): Carbon
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        $timeStr = $this->start_time;
        return Carbon::parse("{$dateStr} {$timeStr}", $tz);
    }

    public function endsAt(): Carbon
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        $timeStr = $this->end_time;
        return Carbon::parse("{$dateStr} {$timeStr}", $tz);
    }

    public function isStarted(): bool
    {
        return Carbon::now(config('app.timezone', 'Asia/Jakarta'))->gte($this->startsAt());
    }

    public function isFinished(): bool
    {
        return Carbon::now(config('app.timezone', 'Asia/Jakarta'))->gte($this->endsAt());
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null || $this->status === 'cancelled';
    }

    public function filledCount(): int
    {
        return $this->participants()->whereIn('status', ['registered', 'attended'])->count();
    }

    public function fillPercentage(): int
    {
        if (!$this->capacity || $this->capacity === 0) {
            return 0;
        }
        return (int) min(100, round(($this->filledCount() / $this->capacity) * 100));
    }

    public function activeParticipantsCount(): int
    {
        return $this->participants()->whereIn('status', ['registered', 'attended'])->count();
    }

    public function remainingSlots(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }
        return max(0, $this->capacity - $this->activeParticipantsCount());
    }

    public function syncStatus(): void
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return;
        }

        if ($this->capacity === null || $this->capacity <= 0) {
            $newStatus = 'open';
        } else {
            $activeCount = $this->activeParticipantsCount();
            $ratio = $activeCount / $this->capacity;
            if ($activeCount >= $this->capacity) {
                $newStatus = 'full';
            } elseif ($ratio >= 0.8) {
                $newStatus = 'almost_full';
            } else {
                $newStatus = 'open';
            }
        }

        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            $this->save();
        }
    }

    public function markNoShows(): void
    {
        if (!config('schedules.no_show_enforcement', false)) {
            return;
        }

        if (!$this->isFinished() || $this->isCancelled()) {
            return;
        }

        DB::transaction(function () {
            $unattended = $this->participants()
                ->where('status', 'registered')
                ->lockForUpdate()
                ->get();

            if ($unattended->isEmpty()) {
                return;
            }

            $userIds = $unattended->pluck('user_id')->unique();

            $this->participants()
                ->where('status', 'registered')
                ->update([
                    'status' => 'no_show',
                    'updated_at' => now(),
                ]);

            $windowDays = config('schedules.no_show_window_days', 30);
            $threshold = config('schedules.no_show_threshold', 3);
            $durationMonths = config('schedules.block_duration_months', 1);
            $tz = config('app.timezone', 'Asia/Jakarta');
            $cutoffDate = Carbon::now($tz)->subDays($windowDays)->toDateString();

            foreach ($userIds as $userId) {
                $user = User::where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    continue;
                }

                $noShowCount = Participant::where('user_id', $userId)
                    ->where('participatable_type', Schedule::class)
                    ->where('status', 'no_show')
                    ->whereHasMorph('participatable', [Schedule::class], function ($q) use ($cutoffDate) {
                        $q->whereDate('date', '>=', $cutoffDate);
                    })
                    ->count();

                if ($noShowCount >= $threshold) {
                    if (!$user->join_blocked_until || $user->join_blocked_until->isPast()) {
                        $user->update([
                            'join_blocked_until' => Carbon::now($tz)->addMonths($durationMonths),
                        ]);
                    }
                }
            }
        });
    }
}
