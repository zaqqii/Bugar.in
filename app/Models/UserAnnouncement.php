<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnnouncement extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'notifiable_type',
        'notifiable_id',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
