@extends('layouts.penyedia-komunitas')

@section('title', 'Dashboard Penyedia Komunitas - Bugar.in')

@section('content')

    {{-- EMPTY STATE — Community belum dibuat --}}
    @if(!$community)
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 rounded-2xl bg-primary-50 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-10 h-10 text-primary-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Komunitas Belum Dibuat</h2>
            <p class="text-slate-500 max-w-md mb-8">
                Kamu belum memiliki komunitas olahraga. Buat komunitas pertamamu dan mulai kelola anggota, event, serta jadwal latihan.
            </p>
            <a href="#" class="btn btn--primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Komunitas Sekarang
            </a>
        </div>
    @else

        {{-- PAGE HEADER --}}
        <div class="admin-page-header">
            <div class="admin-page-header__left">
                <div class="admin-page-header__badge">
                    <span class="admin-page-header__badge-dot"></span>
                    PORTAL HOST RESMI
                    &bull;
                    {{ strtoupper($community->sport_category) }}
                    &bull;
                    {{ strtoupper($community->verification_status) }}
                </div>
                <h1 class="admin-page-header__title">
                    Selamat Datang, Admin {{ $community->name }}
                    <span class="animate-wave inline-block">👋</span>
                </h1>
                <p class="admin-page-header__subtitle">
                    Kelola kegiatan, pantau perkembangan anggota, dan buat jadwal olahraga mingguan dari satu kontrol pusat.
                    @if($community->city)
                        <span class="inline-flex items-center gap-1 text-sm text-slate-400 ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $community->city }}{{ $community->province ? ', ' . $community->province : '' }}
                        </span>
                    @endif
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
                <a href="{{ route('penyedia-komunitas.events.create') }}" class="btn btn--primary btn--sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Event Baru
                </a>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="admin-stats-grid">

            <x-penyedia-komunitas.stat-card
                title="Anggota Aktif"
                value="{{ number_format($stats['active_members']) }}"
                subvalue="member"
                badgeText="{{ number_format($stats['followers']) }} followers"
                badgeType="primary"
                iconBgClass="bg-blue-100 text-blue-600">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </x-slot>
            </x-penyedia-komunitas.stat-card>

            <x-penyedia-komunitas.stat-card
                title="Peserta Kegiatan Aktif"
                value="{{ number_format($stats['active_participants_month']) }}"
                subvalue="peserta bulan ini"
                badgeText="{{ number_format($stats['total_active_events']) }} event aktif"
                badgeType="success"
                iconBgClass="bg-cyan-100 text-cyan-600">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </x-slot>
            </x-penyedia-komunitas.stat-card>

            <x-penyedia-komunitas.stat-card
                title="Rating Komunitas"
                value="{{ $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1) : '—' }}"
                subvalue="/ 5.0"
                badgeText="<svg viewBox='0 0 24 24' fill='currentColor'><path d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg> {{ number_format($stats['ratings_count']) }} ulasan"
                badgeType="primary-light"
                iconBgClass="bg-yellow-100 text-yellow-600">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </x-slot>
            </x-penyedia-komunitas.stat-card>

            @php
                $verificationBadge = match ($community->verification_status) {
                    'verified' => ['label' => '✓ Terverifikasi', 'type' => 'success'],
                    'pending' => ['label' => '⏳ Menunggu Verifikasi', 'type' => 'primary-light'],
                    'rejected' => ['label' => '✗ Ditolak', 'type' => 'danger'],
                    'suspended' => ['label' => '⚠ Disuspend', 'type' => 'primary-light'],
                    default => ['label' => $community->verification_status, 'type' => 'primary'],
                };
            @endphp
            <x-penyedia-komunitas.stat-card
                title="Status Komunitas"
                value="{{ ucfirst($community->sport_category) }}"
                subvalue="kategori"
                badgeText="{{ $verificationBadge['label'] }}"
                badgeType="{{ $verificationBadge['type'] }}"
                iconBgClass="bg-cyan-100 text-cyan-600">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </x-slot>
            </x-penyedia-komunitas.stat-card>

        </div>

        {{-- BOTTOM GRID --}}
        <div class="admin-dashboard-grid">

            {{-- LEFT: Event & Jadwal --}}
            <div class="admin-card admin-events">
                <div class="admin-card__header">
                    <div class="admin-card__title-group">
                        <h2 class="admin-card__title">Event Aktif &amp; Mendatang</h2>
                        <p class="admin-card__subtitle">Jadwal sesi olahraga yang sedang membuka registrasi atau siap dilaksanakan</p>
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

                @if($upcomingItems->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-center">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-primary-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <p class="text-slate-600 font-semibold text-sm">Belum ada event atau jadwal aktif</p>
                        <p class="text-slate-400 text-xs mt-1">Buat event baru untuk mulai menerima peserta</p>
                    </div>
                @else
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>NAMA EVENT &amp; JADWAL</th>
                                    <th>LOKASI / VENUE</th>
                                    <th>SLOT KUOTA</th>
                                    <th>STATUS</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingItems as $item)
                                    <tr>
                                        <td>
                                            <div class="event-info">
                                                <div class="event-date">
                                                    <span class="month">{{ $item['date_month'] }}</span>
                                                    <span class="day">{{ $item['date_day'] }}</span>
                                                </div>
                                                <div class="event-details">
                                                    <strong class="event-title">{{ $item['title'] }}</strong>
                                                    <span class="event-time">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $item['time_label'] }}
                                                        @if($item['type'] === 'schedule')
                                                            <span class="ml-1 text-xs text-slate-400">(Rutin)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="event-location">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $item['location'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="event-quota">
                                                <div class="quota-text">
                                                    <strong>{{ $item['slots_filled'] }} / {{ $item['slots_total'] }}</strong>
                                                    <span class="quota-percentage"
                                                          style="color: {{ $item['is_full'] ? 'var(--color-danger-500)' : 'var(--color-primary-500)' }}">
                                                        {{ $item['is_full'] ? 'Penuh' : $item['percentage'] }}
                                                    </span>
                                                </div>
                                                <div class="quota-bar">
                                                    <div class="quota-fill"
                                                         style="width: {{ $item['percentage'] }};
                                                                background-color: {{ $item['is_full'] ? 'var(--color-danger-500)' : 'var(--color-primary-500)' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge"
                                                  style="background-color: {{ $item['status_color'] }}; color: var(--color-gray-800);">
                                                <span class="status-dot" style="background-color: {{ $item['status_dot_color'] }};"></span>
                                                {{ $item['status_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn--outline btn--sm"
                                                    style="border-color: var(--color-gray-300); color: var(--color-primary-600);">
                                                Kelola
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- RIGHT: Pending Approvals --}}
            <div class="admin-card admin-approvals">
                <div class="admin-card__header">
                    <div class="admin-card__title-group">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="status-dot" style="background-color: var(--color-primary-500);"></span>
                            <h2 class="admin-card__title">Permohonan Masuk</h2>
                            @if($pendingMembers->count() > 0)
                                <span class="badge badge--primary">{{ $pendingMembers->count() }} Baru</span>
                            @endif
                        </div>
                        <p class="admin-card__subtitle">
                            Pengguna yang mengajukan permintaan bergabung ke komunitas ini dan menunggu persetujuan.
                        </p>
                    </div>
                </div>

                @if($pendingMembers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-emerald-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-slate-600 font-semibold text-sm">Tidak ada permohonan</p>
                        <p class="text-slate-400 text-xs mt-1">Semua permintaan sudah diproses</p>
                    </div>
                @else
                    <div class="approval-list">
                        @foreach($pendingMembers as $member)
                            @php $applicant = $member->user; @endphp
                            @if($applicant)
                                <div class="approval-item">
                                    <div class="approval-user">
                                        @if($applicant->avatar)
                                            <img src="{{ asset('storage/' . $applicant->avatar) }}"
                                                 alt="{{ $applicant->name }}"
                                                 class="approval-avatar">
                                        @else
                                            <div class="approval-avatar flex items-center justify-center
                                                        bg-gradient-to-br from-primary-400 to-primary-600
                                                        text-white font-bold text-xs">
                                                {{ strtoupper(substr($applicant->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="approval-user-info">
                                            <strong class="approval-name">{{ $applicant->name }}</strong>
                                            <span class="approval-target">
                                                Daftar: {{ $member->joined_at?->diffForHumans() ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="approval-stat">
                                            <span class="stat-label">Sport</span>
                                            <strong class="stat-value">{{ ucfirst($community->sport_category) }}</strong>
                                        </div>
                                    </div>
                                    <div class="approval-actions">
                                        <button class="btn btn--primary btn--sm approval-btn-accept">Terima</button>
                                        <button class="btn btn--white btn--sm approval-btn-reject"
                                                style="color: var(--color-primary-600);">Tolak</button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if($pendingMembers->count() > 1)
                        <button class="btn btn--white btn--sm"
                                style="width: 100%; margin-top: 1rem; color: var(--color-primary-600); font-weight: 700;">
                            Terima Semua ({{ $pendingMembers->count() }})
                        </button>
                    @endif
                @endif

                <div class="tips-card">
                    <div class="tips-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="tips-content">
                        <strong>Tips Host</strong>
                        <p>Aktifkan check-in berbasis QR minimal 15 menit sebelum waktu start untuk menjaga akurasi poin reputasi komunitas Anda.</p>
                    </div>
                </div>
            </div>

        </div>
    @endif

@endsection