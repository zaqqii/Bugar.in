@php
    $upcomingEvents = [
        [
            'date' => 'Okt',
            'day' => '16',
            'title' => 'Morning Recovery Run 5K',
            'time' => 'Rabu, 06:00 WIB',
            'location' => 'GBK Gate 7, Senayan',
            'slots_filled' => 38,
            'slots_total' => 40,
            'percentage' => '95%',
            'status' => 'Pendaftaran Dibuka',
            'status_color' => 'var(--color-primary-100)'
        ],
        [
            'date' => 'Okt',
            'day' => '19',
            'title' => 'Night Speed Interval Session',
            'time' => 'Sabtu, 19:30 WIB',
            'location' => 'Plaza Barat GBK',
            'slots_filled' => 25,
            'slots_total' => 25,
            'percentage' => '100%',
            'status' => 'Siap Jalan',
            'status_color' => 'var(--color-gray-200)'
        ],
        [
            'date' => 'Okt',
            'day' => '27',
            'title' => 'Community Half-Marathon Simulation',
            'time' => 'Minggu, 05:30 WIB',
            'location' => 'Loop SCBD Sudirman',
            'slots_filled' => 18,
            'slots_total' => 50,
            'percentage' => '36%',
            'status' => 'Pendaftaran Dibuka',
            'status_color' => 'var(--color-primary-100)'
        ]
    ];

    $pendingApprovals = [
        [
            'name' => 'Dimas Anggara',
            'target' => 'Half-Marathon Sim',
            'stat_label' => 'PB:',
            'stat_value' => '1h 52m',
            'avatar' => 'https://i.pravatar.cc/150?u=dimas'
        ],
        [
            'name' => 'Siti Nadya',
            'target' => 'Night Speed Interval',
            'stat_label' => 'Pace:',
            'stat_value' => '5:15',
            'avatar' => 'https://i.pravatar.cc/150?u=siti'
        ],
        [
            'name' => 'Rian Prakoso',
            'target' => 'Half-Marathon Sim',
            'stat_label' => 'PB:',
            'stat_value' => '1h 48m',
            'avatar' => 'https://i.pravatar.cc/150?u=rian'
        ]
    ];
@endphp

@extends('layouts.penyedia-komunitas')

@section('title', 'Dashboard Penyedia Komunitas - Bugar.in')

@section('content')
    {{-- Top Header / Greeting Section --}}
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <div class="admin-page-header__badge">
                <span class="admin-page-header__badge-dot"></span>
                PORTAL HOST RESMI &bull; ID: JKT-SR-048
            </div>
            <h1 class="admin-page-header__title">
                Selamat Datang, Admin Jakarta Sunday Runners <span class="wave">👋</span>
            </h1>
            <p class="admin-page-header__subtitle">
                Kelola kegiatan, pantau perkembangan anggota, dan buat jadwal olahraga mingguan dari satu kontrol pusat.
            </p>
        </div>
        <div class="admin-page-header__actions">
            <button class="btn btn--white btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Data Peserta
            </button>
            <button class="btn btn--white btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                Broadcast Pengumuman
            </button>
            <button class="btn btn--primary btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Event Baru
            </button>
        </div>
    </div>

    {{-- Stat Cards Grid --}}
    <div class="admin-stats-grid">
        <x-penyedia-komunitas.stat-card title="Total Anggota & Followers" value="2,450" subvalue="member"
            badgeText="<svg fill='none' viewBox='0 0 24 24' stroke-width='3' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25' /></svg> +12.4%"
            badgeType="success" iconBgClass="bg-blue-100 text-blue-600">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot>
        </x-penyedia-komunitas.stat-card>

        <x-penyedia-komunitas.stat-card title="Peserta Kegiatan Aktif" value="412" subvalue="pelari bulan ini"
            badgeText="<svg fill='none' viewBox='0 0 24 24' stroke-width='3' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25' /></svg> +18%"
            badgeType="success" iconBgClass="bg-cyan-100 text-cyan-600">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </x-slot>
        </x-penyedia-komunitas.stat-card>

        <x-penyedia-komunitas.stat-card title="Tingkat Keaktifan" value="94.2%" subvalue="konsistensi"
            badgeText="<svg viewBox='0 0 24 24' fill='currentColor'><path d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg> Tier Diamond"
            badgeType="primary-light" iconBgClass="bg-blue-100 text-blue-600">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </x-slot>
        </x-penyedia-komunitas.stat-card>

        <x-penyedia-komunitas.stat-card title="Poin Reputasi Host" value="18,920" subvalue="Pts" badgeText="🏆 Peringkat #2"
            badgeType="cyan-light" iconBgClass="bg-cyan-100 text-cyan-600">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            </x-slot>
        </x-penyedia-komunitas.stat-card>
    </div>

    {{-- Bottom Layout Grid --}}
    <div class="admin-dashboard-grid">
        {{-- Left: Event Aktif & Mendatang --}}
        <div class="admin-card admin-events">
            <div class="admin-card__header">
                <div class="admin-card__title-group">
                    <h2 class="admin-card__title">Event Aktif & Mendatang</h2>
                    <p class="admin-card__subtitle">Jadwal sesi olahraga yang sedang membuka registrasi atau siap
                        dilaksanakan</p>
                </div>
                <button class="btn btn--white btn--sm" style="color: var(--color-primary-600); font-weight: 700;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Lihat Semua Jadwal &rarr;
                </button>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>NAMA EVENT & JADWAL</th>
                            <th>LOKASI / VENUE</th>
                            <th>SLOT KUOTA</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingEvents as $event)
                            <tr>
                                <td>
                                    <div class="event-info">
                                        <div class="event-date">
                                            <span class="month">{{ $event['date'] }}</span>
                                            <span class="day">{{ $event['day'] }}</span>
                                        </div>
                                        <div class="event-details">
                                            <strong class="event-title">{{ $event['title'] }}</strong>
                                            <span class="event-time">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $event['time'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="event-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $event['location'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="event-quota">
                                        <div class="quota-text">
                                            <strong>{{ $event['slots_filled'] }} / {{ $event['slots_total'] }}</strong>
                                            <span class="quota-percentage"
                                                style="color: {{ $event['slots_filled'] == $event['slots_total'] ? 'var(--color-danger-500)' : 'var(--color-primary-500)' }}">
                                                {{ $event['slots_filled'] == $event['slots_total'] ? 'Penuh' : $event['percentage'] }}
                                            </span>
                                        </div>
                                        <div class="quota-bar">
                                            <div class="quota-fill"
                                                style="width: {{ $event['percentage'] }}; background-color: {{ $event['slots_filled'] == $event['slots_total'] ? 'var(--color-danger-500)' : 'var(--color-primary-500)' }}">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge"
                                        style="background-color: {{ $event['status_color'] }}; color: var(--color-gray-800);">
                                        <span class="status-dot" style="background-color: var(--color-primary-500);"></span>
                                        {{ $event['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn--outline btn--sm"
                                        style="border-color: var(--color-gray-300); color: var(--color-primary-600);">Kelola</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: Persetujuan Peserta --}}
        <div class="admin-card admin-approvals">
            <div class="admin-card__header">
                <div class="admin-card__title-group">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="status-dot" style="background-color: var(--color-primary-500);"></span>
                        <h2 class="admin-card__title">Persetujuan Peserta</h2>
                        <span class="badge badge--primary">3 Baru</span>
                    </div>
                    <p class="admin-card__subtitle">Peserta yang mendaftar pada sesi bertingkat (Intermediate /
                        Half-Marathon) butuh verifikasi Pace Host.</p>
                </div>
            </div>

            <div class="approval-list">
                @foreach($pendingApprovals as $approval)
                    <div class="approval-item">
                        <div class="approval-user">
                            <img src="{{ $approval['avatar'] }}" alt="{{ $approval['name'] }}" class="approval-avatar">
                            <div class="approval-user-info">
                                <strong class="approval-name">{{ $approval['name'] }}</strong>
                                <span class="approval-target">Target: {{ $approval['target'] }}</span>
                            </div>
                            <div class="approval-stat">
                                <span class="stat-label">{{ $approval['stat_label'] }}</span>
                                <strong class="stat-value">{{ $approval['stat_value'] }}</strong>
                            </div>
                        </div>
                        <div class="approval-actions">
                            <button class="btn btn--primary btn--sm approval-btn-accept">Terima Cepat</button>
                            <button class="btn btn--white btn--sm approval-btn-reject"
                                style="color: var(--color-primary-600);">Tolak</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="btn btn--white btn--sm"
                style="width: 100%; margin-top: 1rem; color: var(--color-primary-600); font-weight: 700;">
                Terima Semua Sekaligus (3)
            </button>

            {{-- Tips Card --}}
            <div class="tips-card">
                <div class="tips-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="tips-content">
                    <strong>Tips Host Pekan Ini</strong>
                    <p>Aktifkan check-in berbasis QR minimal 15 menit sebelum waktu start untuk menjaga akurasi poin
                        reputasi komunitas Anda.</p>
                </div>
            </div>
        </div>
    </div>
@endsection