<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sport_category',
        'current_streak',
        'longest_streak',
        'last_activity_date',
        'streak_started_at',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
        'streak_started_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

