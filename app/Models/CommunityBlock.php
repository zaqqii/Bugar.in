<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'user_id',
        'blocked_by',
        'reason',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}

