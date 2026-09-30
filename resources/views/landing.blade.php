@extends('layouts.app')

@section('title', 'Bugar.in — Temukan Komunitas & Event Olahraga Indonesia')
@section('description', 'Gabung ke 1.200+ komunitas lari, sepeda, basket, badminton, dan padel di sekitarmu. Ikuti event seru, lacak streak, dan raih poin kebebasan.')

@section('content')

{{-- ============================================================
     HERO SECTION
     ============================================================ --}}
<section class="hero" aria-labelledby="hero-heading">

    {{-- Decorative blobs --}}
    <div class="hero__blob hero__blob--1" aria-hidden="true"></div>
    <div class="hero__blob hero__blob--2" aria-hidden="true"></div>

    <div class="container">
        {{-- Badge --}}
        <div class="hero__badge">
            <div class="hero__badge-dot" aria-hidden="true"></div>
            #1 Platform Komunitas Olahraga di Indonesia
        </div>

        {{-- Heading --}}
        <h1 class="hero__heading" id="hero-heading">
            Temukan Komunitas,<br>
            Olahraga Bersama,<br>
            <span class="highlight">dan Bangun Gaya Hidup Aktifmu</span>
        </h1>

        {{-- Subtext --}}
        <p class="hero__subtext">
            Gabung ke 1.200+ komunitas lari, sepeda, basket, badminton, dan padel
            di sekitarmu. Ikuti event seru, lacak streak, dan raih poin kebebasan.
        </p>

        {{-- Search Box (Alpine-powered) --}}
        <div class="hero__search-box"
             x-data="{ olahraga: '', lokasi: '', level: '' }"
             role="search"
             aria-label="Cari komunitas olahraga">

            {{-- Field: Olahraga --}}
            <div class="hero__search-field">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    id="hero-search-olahraga"
                    type="text"
                    x-model="olahraga"
                    placeholder="Cari olahraga (Lari, Padel, dll.)"
                    aria-label="Cari jenis olahraga">
            </div>

            <div class="hero__search-divider" aria-hidden="true"></div>

            {{-- Field: Lokasi --}}
            <div class="hero__search-field">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <input
                    id="hero-search-lokasi"
                    type="text"
                    x-model="lokasi"
                    placeholder="Jakarta Selatan"
                    aria-label="Masukkan lokasi">
            </div>

            <div class="hero__search-divider" aria-hidden="true"></div>

            {{-- Select: Level --}}
            <div class="hero__search-field" style="cursor:pointer;" aria-label="Pilih level kemampuan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/>
                </svg>
                <select x-model="level" aria-label="Level kemampuan" style="border:none;outline:none;background:transparent;font-family:inherit;font-size:0.875rem;color:#64748b;width:100%;cursor:pointer;">
                    <option value="">Semua Level</option>
                    <option value="beginner">Pemula</option>
                    <option value="intermediate">Menengah</option>
                    <option value="advanced">Mahir</option>
                </select>
            </div>

            {{-- Search Button --}}
            <button class="hero__search-btn" id="hero-search-btn" aria-label="Cari komunitas">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Cari
            </button>
        </div>

        {{-- Quick Tags --}}
        <div class="hero__tags" role="list" aria-label="Filter cepat olahraga">
            @foreach(['🏃 Lari', '🏸 Badminton', '🚴 Bersepeda', '🎾 Padel', '🏀 Basket', '⚽ Futsal'] as $tag)
                <button class="hero__tag" role="listitem" aria-label="Cari {{ $tag }}">{{ $tag }}</button>
            @endforeach
        </div>

        {{-- Stats Bar --}}
        <div class="hero__stats" role="region" aria-label="Statistik platform">
            <div class="hero__stat-item">
                <div class="hero__stat-number" id="counter-members">
                    48,000<span>+</span>
                </div>
                <div class="hero__stat-label">Anggota Aktif</div>
            </div>
            <div class="hero__stat-item">
                <div class="hero__stat-number" id="counter-communities">
                    1,250<span>+</span>
                </div>
                <div class="hero__stat-label">Komunitas Terdaftar</div>
            </div>
            <div class="hero__stat-item">
                <div class="hero__stat-number" id="counter-events">
                    320<span>+</span>
                </div>
                <div class="hero__stat-label">Event Bulan Ini</div>
            </div>
            <div class="hero__stat-item">
                <div class="hero__stat-number">
                    4.9<span>★</span>
                </div>
                <div class="hero__stat-label">Rating Pengguna</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     POPULAR COMMUNITIES SECTION
     ============================================================ --}}
<section class="section communities-section" id="komunitas" aria-labelledby="communities-heading">
    <div class="container">

        {{-- Section Header --}}
        <div class="section-header">
            <div class="section-header__left">
                <div class="section-badge" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                    </svg>
                    Jelajahi Olahraga Kesukaanmu
                </div>
                <h2 class="section-title" id="communities-heading">Komunitas Olahraga Terpopuler</h2>
                <p class="section-subtitle">Temukan ratusan komunitas aktif yang siap menyambutmu bergabung dan berolahraga bersama.</p>
            </div>
            <a href="#" class="section-link" aria-label="Lihat semua komunitas">
                Lihat Semua Komunitas (1,250+)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        {{-- Community Cards Grid --}}
        <div class="community-grid" role="list" aria-label="Daftar komunitas terpopuler">
            @foreach($popularCommunities as $community)
                <article class="community-card" role="listitem" id="community-{{ $community['id'] }}">

                    {{-- Card Image --}}
                    <div class="community-card__image">
                        <img
                            src="{{ $community['cover'] }}"
                            alt="{{ $community['name'] }} community cover"
                            loading="lazy">
                        <span class="community-card__badge community-card__badge--{{ $community['sport_key'] }}"
                              aria-label="Kategori olahraga: {{ $community['sport'] }}">
                            {{ $community['sport'] }}
                        </span>
                        <div class="community-card__rating" aria-label="Rating {{ $community['rating'] }} bintang">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/>
                            </svg>
                            {{ $community['rating'] }}
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="community-card__body">
                        <div class="community-card__meta" aria-label="Lokasi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $community['location'] }}
                        </div>

                        <h3 class="community-card__title">{{ $community['name'] }}</h3>
                        <p class="community-card__desc">{{ $community['description'] }}</p>



                        <div class="community-card__footer">
                            <div class="community-card__stats">
                                <div class="community-card__stat" aria-label="{{ $community['members'] }} anggota">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $community['members'] }}
                                </div>
                                <div class="community-card__stat" aria-label="{{ $community['followers'] }} follower">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ $community['followers'] }}
                                </div>
                            </div>
                            <a href="{{ route('auth.selection') }}"
                               class="community-card__join"
                               id="join-community-{{ $community['id'] }}"
                               aria-label="Bergabung dengan {{ $community['name'] }}"
                               style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
                                Gabung
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     EVENTS SECTION
     ============================================================ --}}
<section class="section events-section" id="events" aria-labelledby="events-heading"
         x-data="{ activeTab: 'semua' }">
    <div class="container">

        {{-- Section Header --}}
        <div class="section-header">
            <div class="section-header__left">
                <div class="section-badge" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/>
                    </svg>
                    Acara Komunitas
                </div>
                <h2 class="section-title" id="events-heading">Event & Fun Match Terdekat</h2>
                <p class="section-subtitle">Daftar sekarang sebelum slot penuh. Ikuti event seru dari komunitas-komunitas favoritmu.</p>
            </div>

            {{-- Tab Filter --}}
            <div class="events-tabs" role="tablist" aria-label="Filter event">
                <button class="events-tab"
                        :class="{ 'active': activeTab === 'semua' }"
                        @click="activeTab = 'semua'"
                        role="tab"
                        :aria-selected="activeTab === 'semua'"
                        id="tab-semua">
                    Semua
                </button>
                <button class="events-tab"
                        :class="{ 'active': activeTab === 'minggu' }"
                        @click="activeTab = 'minggu'"
                        role="tab"
                        :aria-selected="activeTab === 'minggu'"
                        id="tab-minggu">
                    Minggu Ini
                </button>
                <button class="events-tab"
                        :class="{ 'active': activeTab === 'akhirpekan' }"
                        @click="activeTab = 'akhirpekan'"
                        role="tab"
                        :aria-selected="activeTab === 'akhirpekan'"
                        id="tab-akhirpekan">
                    Akhir Pekan
                </button>
            </div>
        </div>

        {{-- Event Grid --}}
        <div class="event-grid" role="list" aria-label="Daftar event terdekat">
            @foreach($upcomingEvents as $event)
                <article class="event-card {{ $event['featured'] ? 'event-card--featured' : '' }}"
                         role="listitem"
                         id="event-{{ $event['id'] }}">

                    {{-- Price badge --}}
                    @if($event['fee_value'] === 0)
                        <span class="event-card__free-badge">Gratis</span>
                    @else
                        <span class="event-card__price-badge">{{ $event['fee'] }}</span>
                    @endif

                    <div class="event-card__community">{{ $event['community'] }}</div>
                    <h3 class="event-card__title">{{ $event['title'] }}</h3>

                    <div class="event-card__details">
                        <div class="event-card__detail" aria-label="Tanggal dan waktu">
                            <svg class="event-card__detail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            {{ $event['date'] }}
                        </div>
                        <div class="event-card__detail" aria-label="Lokasi">
                            <svg class="event-card__detail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $event['location'] }}
                        </div>
                    </div>

                    <div class="event-card__footer">
                        <div class="event-card__slots {{ $event['slots_used'] >= $event['slots_total'] * 0.8 ? 'event-card__slots--low' : '' }}"
                             aria-label="Slot pendaftaran">
                            Slot Peserta:
                            <strong>{{ $event['slots_used'] }} / {{ $event['slots_total'] }}</strong>
                        </div>
                        <button class="event-card__cta"
                                id="register-event-{{ $event['id'] }}"
                                aria-label="Daftar event {{ $event['title'] }}">
                            Daftar Slot →
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     GAMIFICATION SECTION
     ============================================================ --}}
<section class="section gamification-section" id="leaderboard" aria-labelledby="gamification-heading">
    <div class="container">
        <div class="gamification-layout">

            {{-- Left: Features --}}
            <div>
                <div class="section-badge" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                    </svg>
                    Gamifikasi & Reward
                </div>
                <h2 class="section-title" id="gamification-heading" style="margin-bottom: 0.5rem;">
                    Gerak Lebih Banyak, Raih<br>Reward Nyata Setiap Minggu
                </h2>
                <p class="section-subtitle" style="margin-bottom: 2rem;">
                    Setiap kali kamu hadir di event komunitas, sistemmu otomatis mencatat streak istimewamu.
                    Nikmati peringkat kebebasan, dan tukarkan poin dengan voucher apresiasi & gear favoritmu.
                </p>

                <div class="gamification-features">
                    <div class="gamification-feature">
                        <div class="gamification-feature__icon gamification-feature__icon--qr" aria-hidden="true">📱</div>
                        <div>
                            <div class="gamification-feature__title">Check-in Presesi Digital</div>
                            <p class="gamification-feature__desc">
                                Owner scan QR code unik milikmu untuk absensi cepat di lokasi. Otomatis tercatat, tidak perlu antri panjang.
                            </p>
                        </div>
                    </div>
                    <div class="gamification-feature">
                        <div class="gamification-feature__icon gamification-feature__icon--streak" aria-hidden="true">🔥</div>
                        <div>
                            <div class="gamification-feature__title">Leaderboard Aktif Komunitas</div>
                            <p class="gamification-feature__desc">
                                Pantau posisimu setiap minggu lewat leaderboard komunitas. Ajak teman masuk top 10 dan menangkan hadiah eksklusif.
                            </p>
                        </div>
                    </div>
                    <div class="gamification-feature">
                        <div class="gamification-feature__icon gamification-feature__icon--community" aria-hidden="true">🏆</div>
                        <div>
                            <div class="gamification-feature__title">Kalkulator BMI Terintegrasi</div>
                            <p class="gamification-feature__desc">
                                Catat dan pantau progres BMI-mu dari waktu ke waktu. Lihat perubahan kesehatanmu seiring aktifmu berolahraga.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Widgets --}}
            <div class="gamification-widgets">

                {{-- Active Streak Widget --}}
                <div class="streak-widget" x-data="{ days: 7 }" role="region" aria-label="Streak aktivitas">
                    <div class="streak-widget__label">Active Streak</div>
                    <div class="streak-widget__number">
                        <div class="streak-widget__count">7</div>
                        <div class="streak-widget__unit">Hari Berturut-turut 🔥</div>
                    </div>
                    <div class="streak-widget__dots" role="list" aria-label="Hari streak">
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Senin - aktif">S</div>
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Selasa - aktif">S</div>
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Rabu - aktif">R</div>
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Kamis - aktif">K</div>
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Jumat - aktif">J</div>
                        <div class="streak-dot streak-dot--active" role="listitem" aria-label="Sabtu - aktif">S</div>
                        <div class="streak-dot streak-dot--today"  role="listitem" aria-label="Minggu - hari ini">M</div>
                    </div>
                    <div class="streak-widget__next">
                        Hadir besok untuk streak 8 hari! Bonus 100 poin menanti.
                    </div>
                </div>

                {{-- Points Widget --}}
                <div class="points-widget" role="region" aria-label="Total poin">
                    <div class="points-widget__header">
                        <span class="points-widget__label">Poin Terkumpul</span>
                        <span class="points-widget__period" aria-label="Periode bulan ini">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Bulan ini
                        </span>
                    </div>
                    <div class="points-widget__total">
                        <div class="points-widget__number">2,450</div>
                        <div class="points-widget__unit">pts</div>
                    </div>
                    <div class="points-widget__change">↑ +219 poin (Okt, Jumat)</div>
                    <div class="points-widget__bar" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100" aria-label="Progres ke level berikutnya">
                        <div class="points-widget__bar-fill" style="width: 82%;"></div>
                    </div>
                    <div class="points-widget__progress-label">82% menuju Gold Member (3.000 pts)</div>
                </div>

                {{-- Mini Leaderboard --}}
                <div class="leaderboard-mini" role="region" aria-labelledby="leaderboard-mini-title">
                    <div class="leaderboard-mini__header">
                        <div class="leaderboard-mini__title" id="leaderboard-mini-title">Top Komunitas Teraktif Minggu Ini</div>
                        <div class="leaderboard-mini__period">Wilayah: Jabodetabek</div>
                    </div>
                    @foreach($leaderboard as $item)
                        <div class="leaderboard-item" role="listitem" aria-label="Peringkat {{ $item['rank'] }}: {{ $item['name'] }}">
                            <div class="leaderboard-item__rank leaderboard-item__rank--{{ $item['rank_key'] }}"
                                 aria-label="Peringkat {{ $item['rank'] }}">
                                {{ $item['rank'] }}
                            </div>
                            <div class="leaderboard-item__avatar"
                                 style="background: linear-gradient(135deg, #e0f7fa, #00bcd0); display:flex; align-items:center; justify-content:center; font-size:14px;"
                                 aria-hidden="true">
                                {{ $item['rank'] === 1 ? '🏃' : '🏓' }}
                            </div>
                            <div class="leaderboard-item__info">
                                <div class="leaderboard-item__name">{{ $item['name'] }}</div>
                                <div class="leaderboard-item__meta">{{ $item['meta'] }}</div>
                            </div>
                            <div class="leaderboard-item__points" aria-label="{{ $item['points_text'] }}">
                                {{ $item['points_text'] }}
                            </div>
                        </div>
                    @endforeach

                    <div style="padding: 0.75rem 1.25rem; border-top: 1px solid var(--color-gray-100);">
                        <a href="#" class="section-link" style="font-size: 0.8125rem;" aria-label="Lihat leaderboard lengkap">
                            Buka Reward Store →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CTA SECTION
     ============================================================ --}}
<section class="section cta-section" aria-labelledby="cta-heading">
    <div class="container">
        <div class="cta-inner">
            <div>
                <div class="cta-badge" aria-hidden="true">
                    ✦ Untuk Kelola Komunitas
                </div>
                <h2 class="cta-title" id="cta-heading">
                    Kelola Komunitas Olahragamu<br>Tanpa Ribet
                </h2>
                <p class="cta-subtitle">
                    Gunakan fitur broadcast, generate QR otomatis, absensi di lokasi, dan broadcast
                    jadwal langsung ke anggota dalam satu dashboard praktis.
                </p>
                <div class="cta-actions">
                    <a href="#" class="btn btn--white btn--lg" id="cta-daftarkan-komunitas">
                        Daftarkan Komunitas (Gratis)
                    </a>
                    <a href="#" class="btn btn--ghost btn--lg" id="cta-pelajari-fitur">
                        Pelajari Fitur Host →
                    </a>
                </div>
            </div>

            <div class="cta-features">
                <div class="cta-feature-item">
                    <div class="cta-feature-icon" aria-hidden="true">📱</div>
                    <div>
                        <div class="cta-feature-text">Check-in QR di Lokasi</div>
                        <div class="cta-feature-desc">Scan QR unik anggota untuk absensi instan</div>
                    </div>
                </div>
                <div class="cta-feature-item">
                    <div class="cta-feature-icon" aria-hidden="true">📊</div>
                    <div>
                        <div class="cta-feature-text">Dashboard Analitik</div>
                        <div class="cta-feature-desc">Statistik anggota, keaktifan, dan event real-time</div>
                    </div>
                </div>
                <div class="cta-feature-item">
                    <div class="cta-feature-icon" aria-hidden="true">🏆</div>
                    <div>
                        <div class="cta-feature-text">Kelola Event & Turnamen</div>
                        <div class="cta-feature-desc">Buat fun match, turnamen, dan jadwal latihan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Animated number counter for stats
    function animateCounter(el, target, duration = 1500) {
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // cubic ease out
            const current = Math.floor(eased * target);
            el.textContent = current.toLocaleString('id-ID');
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }

    // Intersection Observer for counter animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                const el = entry.target.querySelector('[data-counter]');
                if (el) {
                    animateCounter(el, parseInt(el.dataset.counter));
                }
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.hero__stat-item').forEach(el => observer.observe(el));


</script>
@endpush
