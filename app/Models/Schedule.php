<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'location',
        'maps_url',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'is_recurring',
        'is_active',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function participants()
    {
        return $this->morphMany(Participant::class, 'participatable');
    }

    public function dayLabel(): string
    {
        $map = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        return $map[$this->day_of_week] ?? ucfirst($this->day_of_week);
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
}