<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'user_id',
        'participatable_type',
        'participatable_id',
        'status',
        'qr_code',
        'notes',
        'registered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participatable()
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registered', 'attended']);
    }
}