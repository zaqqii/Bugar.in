<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    /**
     * Pemetaan status laporan ke label Bahasa Indonesia
     */
    public const STATUS_LABELS = [
        'pending'      => 'Belum Dicek',
        'under_review' => 'Sedang Ditinjau',
        'resolved'     => 'Selesai',
        'dismissed'    => 'Ditolak',
    ];

    /**
     * Pemetaan kelas Tailwind badge status untuk UI Admin
     */
    public const STATUS_BADGE_CLASSES = [
        'pending'      => 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
        'under_review' => 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
        'resolved'     => 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
        'dismissed'    => 'bg-slate-700 text-slate-300 border border-slate-600',
    ];

    /**
     * Dapatkan label status Bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? str_replace('_', ' ', $this->status);
    }

    /**
     * Dapatkan kelas badge status Tailwind
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'bg-slate-800 text-slate-300 border border-slate-700';
    }

    /**
     * Format nilai status ke label Bahasa Indonesia (static helper)
     */
    public static function formatStatus(?string $status): string
    {
        if (!$status) {
            return '-';
        }
        return self::STATUS_LABELS[$status] ?? str_replace('_', ' ', $status);
    }

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

