<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bugar.in - Komunitas Olahraga Indonesia</title>

   @vite(['resources/css/landing.css'])
   
</head>

<body>

<main class="landing">

    {{-- ================= HERO ================= --}}
    <section class="hero">

        <div class="hero-glow hero-glow-main"></div>
        <div class="hero-glow hero-glow-right"></div>
        <div class="hero-glow hero-glow-left"></div>

        <div class="container hero-container">

            <div class="hero-badge">
                <span>⚡</span>
                #1 Platform Komunitas Olahraga di Indonesia
            </div>

            <h1>
                Temukan Komunitas, Olahraga Bersama,
                <br>
                dan Bangun Gaya Hidup Aktifmu
            </h1>

            <p class="hero-description">
                Gabung ke 1.200+ komunitas lari, sepeda, basket, badminton,
                futsal, dan padel di sekitarmu.
                Ikuti event seru, lacak streak, dan raih poin kebugaran.
            </p>

            {{-- SEARCH --}}
            <form class="search-box">

                <div class="search-input">
                    <span>⌕</span>

                    <input
                        type="text"
                        placeholder="Cari olahraga (Lari, Padel, dll.)"
                    >
                </div>

                <select>
                    <option>Jakarta Selatan</option>
                    <option>Jakarta Pusat</option>
                    <option>Jakarta Barat</option>
                    <option>Jakarta Timur</option>
                </select>

                <select>
                    <option>Semua Level</option>
                    <option>Pemula</option>
                    <option>Menengah</option>
                    <option>Advanced</option>
                </select>

                <button type="submit" class="btn-primary">
                    🔎 Cari
                </button>

            </form>

            {{-- QUICK SEARCH --}}
            <div class="quick-search">

                <strong>🔥 POPULER:</strong>

                <span>Padel Senayan</span>
                <span>GBK Runners</span>
                <span>Bintaro Cyclist</span>
                <span>Tebet Futsal Club</span>

            </div>

            {{-- STATS --}}
            <div class="stats">

                <div class="stat">
                    <strong>48,000+</strong>
                    <span>Anggota Aktif</span>
                </div>

                <div class="stat stat-cyan">
                    <strong>1,250+</strong>
                    <span>Komunitas Terverifikasi</span>
                </div>

                <div class="stat">
                    <strong>320+</strong>
                    <span>Event Bulan Ini</span>
                </div>

                <div class="stat">
                    <strong>4.9 ⭐</strong>
                    <span>Rating Kepuasan</span>
                </div>

            </div>

        </div>
    </section>


    {{-- ================= COMMUNITY ================= --}}
    <section class="section community-section">

        <div class="container">

            <div class="section-header">

                <div>
                    <span class="section-label">
                        🏆 JELAJAHI CIRCLE KEBUGARAN
                    </span>

                    <h2>Komunitas Olahraga Terpopuler</h2>

                    <p>
                        Temukan rekan latihan konsisten dengan jadwal rutin
                        dan host kredibel.
                    </p>
                </div>

                <a href="#" class="section-link">
                    Lihat Semua Komunitas (1,250+) →
                </a>

            </div>


            <div class="community-grid">

                @php
                    $communities = [
                        [
                            'image' => 'communities/running.jpg',
                            'sport' => 'Running',
                            'location' => 'GBK, Jakarta Pusat',
                            'rating' => '4.9 (1.4k)',
                            'name' => 'Jakarta Sunday Runners',
                            'description' => 'Komunitas lari santai 5K-10K mingguan untuk pemula hingga marathon finisher.',
                            'schedule' => 'Minggu, 06:30 WIB @ Gate 5 GBK',
                            'members' => '1,420 Member'
                        ],
                        [
                            'image' => 'communities/padel.jpg',
                            'sport' => 'Padel',
                            'location' => 'Senayan, Jaksel',
                            'rating' => '4.8 (850)',
                            'name' => 'Sudirman Padel Society',
                            'description' => 'Networking & fun padel matches rutin 3x seminggu di arena Senayan & SCBD.',
                            'schedule' => 'Rabu & Sabtu, 19:00 WIB',
                            'members' => '890 Member'
                        ],
                        [
                            'image' => 'communities/cycling.jpg',
                            'sport' => 'Cycling',
                            'location' => 'Dago, Bandung',
                            'rating' => '5.0 (620)',
                            'name' => 'Bandung Peloton Squad',
                            'description' => 'Gowes santai tanjakan Lembang & ride keliling kota dengan format peleton tertib.',
                            'schedule' => 'Sabtu, 05:45 WIB @ Simpang Dago',
                            'members' => '620 Member'
                        ],
                        [
                            'image' => 'communities/badminton.jpg',
                            'sport' => 'Badminton',
                            'location' => 'Margonda, Depok',
                            'rating' => '4.9 (1.1k)',
                            'name' => 'Depok Smashers Club',
                            'description' => 'Mabar bulutangkis santai ganda & tunggal terbuka untuk semua level kemahiran.',
                            'schedule' => 'Selasa & Jumat, 19:30 WIB',
                            'members' => '1,150 Member'
                        ]
                    ];
                @endphp


                @foreach ($communities as $community)

                    <article class="community-card">

                        <div class="community-image">

                            <img
                                src="{{ asset('images/' . $community['image']) }}"
                                alt="{{ $community['name'] }}"
                            >

                            <span class="sport-badge">
                                {{ $community['sport'] }}
                            </span>

                            <span class="verified">
                                ✓ Terverifikasi
                            </span>

                        </div>


                        <div class="community-content">

                            <div class="community-meta">
                                <span>
                                    📍 {{ $community['location'] }}
                                </span>

                                <span>
                                    ⭐ {{ $community['rating'] }}
                                </span>
                            </div>

                            <h3>
                                {{ $community['name'] }}
                            </h3>

                            <p>
                                {{ $community['description'] }}
                            </p>


                            <div class="community-info">

                                <strong>
                                    📅 {{ $community['schedule'] }}
                                </strong>

                                <div class="community-bottom">

                                    <span>
                                        {{ $community['members'] }}
                                    </span>

                                    <button>
                                        Gabung
                                    </button>

                                </div>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        </div>

    </section>


    {{-- ================= EVENTS ================= --}}
    <section class="section events-section">

        <div class="container">

            <div class="section-header">

                <div>

                    <span class="section-label cyan">
                        📅 JADWAL KOMUNITAS
                    </span>

                    <h2>
                        Event & Fun Match Terdekat
                    </h2>

                    <p>
                        Daftar slot latihan bersama, fun match akhir pekan,
                        atau turnamen komunitas lokal.
                    </p>

                </div>


                <div class="event-filter">

                    <button class="active">
                        Semua
                    </button>

                    <button>
                        Minggu Ini
                    </button>

                    <button>
                        Weekend
                    </button>

                </div>

            </div>


            <div class="event-grid">

                @php
                    $events = [
                        [
                            'category' => 'Lari Santai',
                            'title' => 'GBK Night Pace 5.5K',
                            'price' => 'Gratis',
                            'host' => 'Jakarta Sunday Runners',
                            'date' => 'Kamis, 24 Okt • 19:30 - 21:00 WIB',
                            'location' => 'Plaza Barat GBK, Senayan',
                            'filled' => 18,
                            'capacity' => 24
                        ],
                        [
                            'category' => 'Padel Fun Match',
                            'title' => 'Beginner Americano Mini Tourney',
                            'price' => 'Rp 65.000',
                            'host' => 'Sudirman Padel Society',
                            'date' => 'Sabtu, 26 Okt • 16:00 - 18:30 WIB',
                            'location' => 'Padel Pro Court SCBD, Jakarta',
                            'filled' => 14,
                            'capacity' => 16
                        ],
                        [
                            'category' => 'Badminton Mabar',
                            'title' => 'Sunday Double Smash Series',
                            'price' => 'Rp 35.000',
                            'host' => 'Depok Smashers',
                            'date' => 'Minggu, 27 Okt • 08:00 - 11:00 WIB',
                            'location' => 'GOR Tirta Jaya, Margonda',
                            'filled' => 10,
                            'capacity' => 20
                        ]
                    ];
                @endphp


                @foreach ($events as $event)

                    @php
                        $percentage = ($event['filled'] / $event['capacity']) * 100;
                    @endphp

                    <article class="event-card">

                        <div class="event-top">

                            <div class="event-category">
                                {{ $event['category'] }}
                            </div>

                            <strong class="event-price">
                                {{ $event['price'] }}
                            </strong>

                        </div>


                        <h3>
                            {{ $event['title'] }}
                        </h3>

                        <span class="event-host">
                            👥 Host: {{ $event['host'] }}
                        </span>


                        <div class="event-detail">

                            <div>
                                🕒 {{ $event['date'] }}
                            </div>

                            <div>
                                📍 {{ $event['location'] }}
                            </div>

                        </div>


                        <div class="quota">

                            <div class="quota-header">

                                <strong>Slot Peserta</strong>

                                <span>
                                    {{ $event['filled'] }} /
                                    {{ $event['capacity'] }}
                                    Terisi
                                </span>

                            </div>

                            <div class="quota-bar">

                                <div
                                    class="quota-progress"
                                    style="width: {{ $percentage }}%"
                                ></div>

                            </div>

                        </div>


                        <button
                            class="event-button {{ $percentage >= 80 ? 'event-button-primary' : '' }}"
                        >
                            {{ $percentage >= 80 ? 'Daftar Slot Sekarang' : 'Daftar Slot' }}
                            →
                        </button>

                    </article>

                @endforeach

            </div>

        </div>

    </section>


    {{-- ================= GAMIFICATION ================= --}}
    <section class="section gamification-section">

        <div class="container gamification-grid">

            <div class="gamification-copy">

                <span class="section-label cyan">
                    🏆 GAMIFIKASI & REWARD
                </span>

                <h2>
                    Gerak Lebih Banyak,
                    Raih Reward Nyata Setiap Minggu
                </h2>

                <p>
                    Setiap kali kamu hadir di event komunitas,
                    sistem otomatis mencatat streak latihanmu.
                    Naikkan peringkat kota, buka lencana kebugaran,
                    dan tukarkan poin dengan voucher apparel & gear favorit.
                </p>


                <div class="feature-list">

                    <div class="feature">

                        <div class="feature-icon">
                            ✓
                        </div>

                        <div>
                            <h4>Check-in Presensi Digital</h4>

                            <p>
                                Verifikasi kehadiran instan via QR code
                                host di venue latihan.
                            </p>
                        </div>

                    </div>


                    <div class="feature">

                        <div class="feature-icon cyan-icon">
                            ⚡
                        </div>

                        <div>
                            <h4>Leaderboard Atlet Komunitas</h4>

                            <p>
                                Bersaing sehat dengan teman satu klub
                                atau antar komunitas.
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            <div class="bento-grid">

                {{-- STREAK --}}
                <div class="bento-card">

                    <div class="bento-header">
                        <span>Active Streak</span>
                        <small>🔥 ON FIRE</small>
                    </div>

                    <div class="streak-number">
                        <strong>7</strong>
                        <span>Hari Berturut-turut</span>
                    </div>

                    <p>
                        Hanya butuh 1 sesi lagi untuk klaim bonus
                        150 Poin Gerak!
                    </p>

                    <div class="week-tracker">

                        @foreach (['S','S','R','K','J','S','M'] as $day)
                            <span class="day {{ $loop->last ? 'today' : '' }}">
                                {{ $day }}
                            </span>
                        @endforeach

                    </div>

                </div>


                {{-- REWARD --}}
                <div class="bento-card">

                    <div class="bento-header">
                        <span>Poin Terkumpul</span>
                        <span>🏆</span>
                    </div>

                    <div class="points">
                        2,450
                        <small>PTS</small>
                    </div>

                    <div class="reward-item">

                        <div class="reward-icon">
                            🎁
                        </div>

                        <div>
                            <strong>
                                Diskon 25% Ortuseight / Mills
                            </strong>

                            <span>
                                Bisa ditukar (2,000 PTS)
                            </span>
                        </div>

                    </div>

                    <button class="reward-button">
                        Buka Reward Store
                    </button>

                </div>


                {{-- LEADERBOARD --}}
                <div class="bento-card leaderboard-card">

                    <div class="leaderboard-header">

                        <div>
                            <h3>
                                Top Komunitas Teraktif Minggu Ini
                            </h3>

                            <p>
                                Peringkat berdasarkan jam partisipasi
                                & total presensi anggota.
                            </p>
                        </div>

                        <span>
                            Jabodetabek
                        </span>

                    </div>


                    <div class="ranking">

                        <div class="rank-item">

                            <strong>1</strong>

                            <div class="avatar">
                                JSR
                            </div>

                            <div class="rank-info">

                                <strong>
                                    Jakarta Sunday Runners
                                </strong>

                                <span>
                                    3,890 Jam Aktif • 348 Check-in
                                </span>

                            </div>

                            <small>
                                +1,200 PTS
                            </small>

                        </div>


                        <div class="rank-item">

                            <strong>2</strong>

                            <div class="avatar cyan-avatar">
                                SPS
                            </div>

                            <div class="rank-info">

                                <strong>
                                    Sudirman Padel Society
                                </strong>

                                <span>
                                    2,910 Jam Aktif • 220 Check-in
                                </span>

                            </div>

                            <small>
                                +850 PTS
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ================= CTA ================= --}}
    <section class="cta-section">

        <div class="container">

            <div class="cta">

                <div class="cta-glow"></div>

                <div class="cta-content">

                    <span class="cta-badge">
                        PUNYA KLUB SENDIRI?
                    </span>

                    <h2>
                        Kelola Komunitas Olahragamu
                        <br>
                        Tanpa Ribet
                    </h2>

                    <p>
                        Gunakan fitur ticketing, presensi QR otomatis,
                        absensi kuota, dan broadcast jadwal langsung
                        ke anggota dalam satu dashboard praktis.
                    </p>


                    <div class="cta-actions">

                        <a href="#" class="cta-button">
                            Daftarkan Komunitas (Gratis)
                        </a>

                        <a href="#" class="cta-link">
                            Pelajari Fitur Host →
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>

</body>
</html>