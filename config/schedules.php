<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aturan & Konfigurasi Jadwal Rutin Bugar.in
    |--------------------------------------------------------------------------
    */

    // Batas waktu minimal (dalam jam) sebelum jadwal dimulai untuk membatalkan pendaftaran
    'cancel_deadline_hours' => 2,

    // Batas maksimal pendaftaran jadwal mendatang aktif per user
    'max_active_bookings' => 3,

    // Ambang batas ketidakhadiran (no-show) sebelum dikenai sanksi blokir pendaftaran
    'no_show_threshold' => 3,

    // Rentang waktu evaluasi no-show (hari)
    'no_show_window_days' => 30,

    // Durasi pemblokiran pendaftaran jadwal (bulan)
    'block_duration_months' => 1,

    // Flag enforcement no-show (default false)
    'no_show_enforcement' => (bool) env('SCHEDULE_NO_SHOW_ENFORCEMENT', false),

    /*
    |--------------------------------------------------------------------------
    | Aturan Check-in QR & Kehadiran Sesi
    |--------------------------------------------------------------------------
    */

    // Waktu jendela check-in sebelum jadwal dimulai (menit)
    'checkin_window_before_minutes' => 30,

    // Durasi kedaluwarsa token QR check-in di cache (detik)
    'checkin_token_ttl_seconds' => 90,

    // Interval rotasi token QR di antarmuka pengguna (detik)
    'checkin_token_rotate_seconds' => 45,

    // Jumlah Gerak Poin yang didapatkan peserta saat check-in terverifikasi
    'attendance_points' => 30,
];

