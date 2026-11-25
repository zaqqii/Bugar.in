<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    /**
     * Daftar status partisipan yang menempati/memegang kursi (slot kapasitas).
     *
     * Catatan Desain (Task PAY-01 & Roadmap Pembayaran):
     * Status 'waiting_payment' saat ini tetap memegang kursi. Kelak ketika model Payment
     * dan PaymentService diintegrasikan, evaluasi kedaluwarsa pembayaran (expires_at)
     * akan ditambahkan di titik ini sehingga 'waiting_payment' yang telah expired
     * tidak lagi memegang kursi.
     */
    public const SEAT_STATUSES = ['registered', 'waiting_payment', 'attended'];

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

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'participant_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registered', 'attended', 'waiting_list', 'waiting_payment']);
    }

    public function scopeHoldingSeat($query)
    {
        return $query->whereIn('status', self::SEAT_STATUSES);
    }

    public function scopeActiveCommitment($query)
    {
        return $query->whereIn('status', self::SEAT_STATUSES);
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
