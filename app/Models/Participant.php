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
        'cancelled_at'  => 'datetime',
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
        return $query->whereIn('status', ['registered', 'attended', 'waiting_list', 'waiting_payment']);
    }

    public function scopeWaitingConfirmation($query)
    {
        return $query->whereIn('status', ['waiting_list', 'waiting_payment']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'registered'      => 'Terdaftar',
            'waiting_list'    => 'Menunggu Konfirmasi',
            'waiting_payment' => 'Menunggu Pembayaran',
            'cancelled'       => 'Dibatalkan',
            'attended'        => 'Hadir',
            'no_show'         => 'Tidak Hadir',
            default           => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'registered'      => 'blue',
            'waiting_list'    => 'amber',
            'waiting_payment' => 'orange',
            'cancelled'       => 'slate',
            'attended'        => 'emerald',
            'no_show'         => 'red',
            default           => 'slate',
        };
    }
}
