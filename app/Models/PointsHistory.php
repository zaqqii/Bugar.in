<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsHistory extends Model
{
    protected $table = 'points_histories';

    protected $fillable = [
        'user_id',
        'points',
        'source',
        'pointable_type',
        'pointable_id',
        'description',
        'balance_after',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pointable()
    {
        return $this->morphTo();
    }
}
