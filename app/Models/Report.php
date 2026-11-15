<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'evidence',
        'status',
        'handled_by',
        'admin_notes',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    /**
     * Pelapor (User)
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Terlapor (Polymorphic: Community, Event, atau User)
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Admin yang menangani laporan
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

