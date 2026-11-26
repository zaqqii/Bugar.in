<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nominal Poin Gerak Bugar.in
    |--------------------------------------------------------------------------
    | Sumber utama konfigurasi nominal penambahan Poin Gerak.
    | Seluruh nominal dan batas harus berada di config, tidak di-hardcode di kode.
    */

    // Poin login harian (+2) untuk role user
    'daily_login' => 2,

    // Poin kehadiran sesi jadwal komunitas (nilai default: 30)
    // Dipertahankan sinkron dengan schedules.attendance_points sebagai fallback
    'schedule_attendance' => 30,

    // Poin kehadiran event (10 poin jika komunitas verified, 5 poin jika tidak)
    'event_attendance_verified' => 10,
    'event_attendance_unverified' => 5,

    /*
    |--------------------------------------------------------------------------
    | Tingkatan Level Pengguna (Berdasarkan users.total_points)
    |--------------------------------------------------------------------------
    | Level 1-5 dihitung akumulatif murni dari total_points pengguna.
    | Skor atau bonus streak TIDAK masuk perhitungan level dan TIDAK menambah saldo.
    */
    'levels' => [
        1 => [
            'name' => 'Pemula',
            'min_points' => 0,
            'max_points' => 199,
        ],
        2 => [
            'name' => 'Aktif',
            'min_points' => 200,
            'max_points' => 499,
        ],
        3 => [
            'name' => 'Konsisten',
            'min_points' => 500,
            'max_points' => 999,
        ],
        4 => [
            'name' => 'Berkembang',
            'min_points' => 1000,
            'max_points' => 1999,
        ],
        5 => [
            'name' => 'All-Rounder',
            'min_points' => 2000,
            'max_points' => null, // Level maksimum
        ],
    ],
];
