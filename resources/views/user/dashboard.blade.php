@extends('layouts.user')

@section('title', 'Dashboard Pengguna - Bugar.in')

@section('content')
<div x-data="{
    activeSection: 'komunitas',
    filterCategory: '',
    filterCity: '',
    searchQuery: '',
    komunitasSubTab: 'komunitas', // 'komunitas' | 'jadwal'

    // BMI Calculator state
    bmiHeight: 170,
    bmiWeight: 65,
    bmiGender: 'l',
    bmiAge: 24,

    get bmiValue() {
        if (!this.bmiHeight || !this.bmiWeight) return 0;
        let h = this.bmiHeight / 100;
        return (this.bmiWeight / (h * h)).toFixed(1);
    },

    get bmiCategory() {
        let v = parseFloat(this.bmiValue);
        if (v === 0) return { label: 'Belum dihitung', color: 'text-stone-500', bg: 'bg-stone-100', desc: 'Masukkan tinggi dan berat badanmu' };
        if (v < 18.5) return { label: 'Kurus (Underweight)', color: 'text-amber-700', bg: 'bg-amber-50', border: 'border-amber-200', desc: 'Tingkatkan asupan nutrisi seimbang & latihan penguatan massa otot.' };
        if (v <= 25.0) return { label: 'Normal (Ideal)', color: 'text-emerald-700', bg: 'bg-emerald-50', border: 'border-emerald-200', desc: 'Luar biasa! Pertahankan berat badan idealmu dengan olahraga rutin.' };
        if (v <= 27.0) return { label: 'Gemuk (Overweight)', color: 'text-orange-700', bg: 'bg-orange-50', border: 'border-orange-200', desc: 'Perbanyak aktivitas aerobik/kardio dan atur porsi makan sehat.' };
        return { label: 'Obesitas', color: 'text-rose-700', bg: 'bg-rose-50', border: 'border-rose-200', desc: 'Konsultasikan dengan dokter atau pelatih untuk program latihan bertahap.' };
    },

    get idealWeightRange() {
        if (!this.bmiHeight) return '-';
        let h = this.bmiHeight / 100;
        let min = (18.5 * h * h).toFixed(1);
        let max = (25.0 * h * h).toFixed(1);
        return `${min} kg - ${max} kg`;
    },

    // Daily Streak Tracker
    todayCheckedIn: false,
    toggleTodayCheckIn() {
        this.todayCheckedIn = !this.todayCheckedIn;
    }
}" class="space-y-8 w-full max-w-7xl">

    {{-- ============================================================
         1. HERO WELCOME BANNER (Gradient Putih ke Merah Pudar & Glass)
         ============================================================ --}}
    <div class="relative overflow-hidden rounded-2xl border border-rose-200/80 bg-gradient-to-r from-white/95 via-rose-50/70 to-red-100/40 p-6 sm:p-8 shadow-xs backdrop-blur-md">
        {{-- Rich decorative blurred ambient gradient orbs --}}
        <div class="absolute -right-12 -top-12 w-80 h-80 rounded-full bg-gradient-to-br from-rose-400/20 via-red-300/15 to-transparent blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-gradient-to-tr from-pink-300/20 via-rose-200/15 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100/70 text-rose-700 border border-rose-200/60 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    Portal Komunitas &amp; Olahraga
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight leading-tight">
                    Semangat Berolahraga, {{ $user->name ?? 'Pengguna Bugar.in' }}! 👋
                </h2>
                <p class="text-stone-600 text-sm sm:text-base mt-2.5 leading-relaxed">
                    Temukan komunitas olahraga lokal terdekat, ikuti jadwal latihan mingguan, atau pantau status kebugaran tubuhmu secara teratur dari satu ruang kontrol.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                <a href="{{ route('user.explore') }}"
                    class="px-5 py-2.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 text-white shadow-xs hover:shadow-sm transition-all duration-150 flex items-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    Jelajah Komunitas
                </a>

                <button type="button"
                    @click="setSection('bmi')"
                    class="px-5 py-2.5 rounded-xl font-semibold text-sm bg-white hover:bg-rose-50/60 text-stone-700 border border-stone-200/90 hover:border-rose-200 shadow-xs transition-all duration-150 flex items-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Hitung BMI Saya
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         2. STAT CARDS (Data Asli dari Database)
         ============================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Komunitas Terdaftar --}}
        <x-user.stat-card
            title="Komunitas Terdaftar"
            :value="$totalCommunitiesCount"
            subvalue="komunitas aktif"
            badgeText="Tersedia"
            badgeType="rose"
            iconBgClass="bg-rose-50 text-rose-600">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7 10a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </x-slot:icon>
        </x-user.stat-card>

        {{-- Card 2: Event Mendatang --}}
        <x-user.stat-card
            title="Event Mendatang"
            :value="$totalEventsCount"
            subvalue="agenda terbuka"
            badgeText="{{ $totalEventsCount > 0 ? 'Tersedia' : 'Belum Ada' }}"
            badgeType="{{ $totalEventsCount > 0 ? 'success' : 'orange' }}"
            iconBgClass="bg-amber-50 text-amber-600">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </x-slot:icon>
        </x-user.stat-card>

        {{-- Card 3: Komunitas Saya --}}
        <x-user.stat-card
            title="Komunitas Saya"
            :value="$userCommunitiesCount"
            subvalue="klub diikuti"
            badgeText="Member"
            badgeType="purple"
            iconBgClass="bg-purple-50 text-purple-600">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </x-slot:icon>
        </x-user.stat-card>

        {{-- Card 4: Jadwal Latihan Rutin --}}
        <x-user.stat-card
            title="Sesi Latihan Rutin"
            :value="$totalSchedulesCount"
            subvalue="sesi mingguan"
            badgeText="Jadwal Aktif"
            badgeType="success"
            iconBgClass="bg-emerald-50 text-emerald-600">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-user.stat-card>
    </div>

    {{-- ============================================================
         3. MODUL 1: EKSPLORASI KOMUNITAS & KEGIATAN OLAHRAGA
         ============================================================ --}}
    <section x-show="activeSection === 'komunitas'" x-cloak class="space-y-6">  
        {{-- Filter & Search Header --}}
        <div class="glass-card rounded-2xl p-5 sm:p-6 border border-rose-100/70 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-rose-100/60">
                <div>
                    <h3 class="text-lg font-bold text-stone-900 tracking-tight">Eksplorasi Komunitas &amp; Jadwal Latihan</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Cari komunitas olahraga berdasarkan cabang olahraga dan lokasi kotamu</p>
                </div>

                {{-- Subtab Switcher --}}
                <div class="flex items-center gap-1.5 bg-stone-100/80 p-1 rounded-xl self-start md:self-auto">
                    <button type="button"
                        @click="komunitasSubTab = 'komunitas'"
                        :class="komunitasSubTab === 'komunitas' ? 'bg-white text-rose-600 font-semibold shadow-xs' : 'text-stone-600 hover:text-stone-900'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                        Daftar Komunitas ({{ $communities->count() }})
                    </button>
                    <button type="button"
                        @click="komunitasSubTab = 'jadwal'"
                        :class="komunitasSubTab === 'jadwal' ? 'bg-white text-rose-600 font-semibold shadow-xs' : 'text-stone-600 hover:text-stone-900'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                        Jadwal Latihan ({{ $schedules->count() }})
                    </button>
                </div>
            </div>

        {{-- SubTab 1: Komunitas Terdaftar --}}
        <div x-show="komunitasSubTab === 'komunitas'" class="space-y-4">
            @if($communities->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-md mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-stone-900 mb-1">Belum Ada Komunitas Terdaftar</h4>
                    <p class="text-xs text-stone-500 leading-relaxed">Komunitas olahraga baru akan muncul di sini setelah didaftarkan oleh host.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($communities as $comm)
                        @php
                            $commCategory = strtolower($comm->sport_category ?? '');
                            $commCity = strtolower($comm->city ?? '');
                            $commNameLower = strtolower($comm->name ?? '');
                        @endphp
                        <div x-show="
                            (!filterCategory || '{{ $commCategory }}'.includes(filterCategory)) &&
                            (!filterCity || '{{ $commCity }}'.includes(filterCity)) &&
                            (!searchQuery || '{{ $commNameLower }}'.includes(searchQuery.toLowerCase()))
                        " class="glass-card rounded-2xl p-5 border border-rose-100/70 shadow-xs hover:shadow-sm hover:border-rose-200 transition-all flex flex-col justify-between">
                            <div>
                                {{-- Badges --}}
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ ucfirst($comm->sport_category ?? 'Olahraga') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-stone-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $comm->city ?? 'Indonesia' }}
                                    </span>
                                </div>

                                {{-- Name & Description --}}
                                <h4 class="text-base font-bold text-stone-900 tracking-tight leading-snug">
                                    {{ $comm->name }}
                                </h4>
                                <p class="text-xs text-stone-500 mt-1.5 line-clamp-2 leading-relaxed">
                                    {{ $comm->description ?? 'Komunitas olahraga aktif yang terbuka untuk anggota baru dan sesi latihan bersama.' }}
                                </p>

                                {{-- Meta Counts --}}
                                <div class="flex items-center gap-4 mt-4 pt-3 border-t border-rose-50 text-[11px] text-stone-500 font-medium">
                                    <span>👥 {{ $comm->members_count ?? 0 }} Anggota</span>
                                    <span>📅 {{ $comm->schedules_count ?? 0 }} Sesi Rutin</span>
                                    <span>🏆 {{ $comm->events_count ?? 0 }} Event</span>
                                </div>
                            </div>

                            {{-- Card Footer --}}
                            <div class="mt-5 pt-3 flex items-center justify-between">
                                <span class="text-[11px] text-stone-400">
                                    {{ $comm->contact_email ?? 'Email terdaftar' }}
                                </span>
                                <button type="button" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100/80 border border-rose-200 transition-colors cursor-pointer">
                                    Lihat Detail &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- SubTab 2: Jadwal Latihan Rutin --}}
        <div x-show="komunitasSubTab === 'jadwal'" class="space-y-4">
            @if($schedules->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-md mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-stone-900 mb-1">Belum Ada Jadwal Latihan Rutin</h4>
                    <p class="text-xs text-stone-500 leading-relaxed">Komunitas belum mempublikasikan jadwal latihan mingguan. Pantau berkala untuk jadwal sesi terbaru.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($schedules as $sch)
                        <div class="glass-card rounded-2xl p-5 border border-rose-100/70 shadow-xs flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-100">
                                        {{ $sch->dayLabel() }}
                                    </span>
                                    <span class="text-xs font-semibold text-stone-600">
                                        {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-stone-900 mt-2">{{ $sch->title }}</h4>
                                <p class="text-xs text-stone-500 mt-1">{{ $sch->community?->name ?? 'Komunitas' }}</p>
                                <p class="text-xs text-stone-600 mt-3 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $sch->location ?? 'Lokasi belum ditentukan' }}
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-rose-50 flex items-center justify-between">
                                <span class="text-xs text-stone-500">Kapasitas: {{ $sch->capacity ?? 'Bebas' }}</span>
                                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-colors">
                                    Ikut Sesi
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
         4. MODUL 2: EKSPLORASI EVENT
         ============================================================ --}}
    <section x-show="activeSection === 'events'" x-cloak class="space-y-6">
        <div class="glass-card rounded-2xl p-5 sm:p-6 border border-rose-100/70 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-stone-900 tracking-tight">Eksplorasi Event Mendatang</h3>
                <p class="text-xs text-stone-500 mt-0.5">Turnamen, fun run, gathering olahraga, dan kompetisi yang akan diselenggarakan</p>
            </div>
            <div class="text-xs font-semibold text-rose-700 bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-200 self-start sm:self-auto">
                Total Agenda: {{ $events->count() }} Event
            </div>
        </div>

        @if($events->isEmpty())
            <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-bold text-stone-900 mb-1.5">Belum Ada Event Mendatang</h4>
                <p class="text-xs sm:text-sm text-stone-500 max-w-md mx-auto leading-relaxed">
                    Komunitas belum mempublikasikan event olahraga baru saat ini. Seluruh pengumuman kompetisi dan gathering akan otomatis tampil di sini.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($events as $event)
                    <div class="glass-card rounded-2xl p-5 border border-rose-100/70 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $event->community?->name ?? 'Komunitas' }}
                                </span>
                                <span class="text-xs font-bold {{ $event->registration_fee > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                    {{ $event->registration_fee > 0 ? 'Rp ' . number_format($event->registration_fee, 0, ',', '.') : 'Gratis' }}
                                </span>
                            </div>
                            <h4 class="text-base font-bold text-stone-900 leading-snug">{{ $event->title }}</h4>
                            <p class="text-xs text-stone-500 mt-1 line-clamp-2">{{ $event->description ?? 'Event olahraga terbuka untuk peserta umum.' }}</p>

                            <div class="mt-4 space-y-1.5 text-xs text-stone-600">
                                <div class="flex items-center gap-2">
                                    <span class="text-stone-400">📅</span>
                                    <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-stone-400">📍</span>
                                    <span class="truncate">{{ $event->location ?? 'Lokasi belum ditentukan' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-rose-50 flex items-center justify-between">
                            <span class="text-[11px] text-stone-500">Kuota: {{ $event->capacity ?? 'Bebas' }} Peserta</span>
                            <button type="button" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-colors">
                                Daftar Event
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>


    {{-- ============================================================
         5. MODUL 3: PENGHITUNG BMI (Humanized & Clean)
         ============================================================ --}}
    <section x-show="activeSection === 'bmi'" x-cloak class="space-y-6">
        <div class="glass-card rounded-2xl p-6 sm:p-8 border border-rose-100/70 shadow-xs">
            <div class="max-w-2xl mb-8">
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-100">
                    Kesehatan &amp; Kebugaran
                </span>
                <h3 class="text-xl font-bold text-stone-900 tracking-tight mt-2">Kalkulator Body Mass Index (BMI)</h3>
                <p class="text-xs sm:text-sm text-stone-500 mt-1 leading-relaxed">
                    Hitung indeks massa tubuhmu menggunakan standar Kementerian Kesehatan RI untuk mengetahui status gizi dan berat badan idealmu.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Form Input --}}
                <div class="lg:col-span-7 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tinggi Badan --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-700">Tinggi Badan (cm)</label>
                            <input type="number"
                                x-model.number="bmiHeight"
                                min="100" max="250"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-stone-800">
                        </div>

                        {{-- Berat Badan --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-700">Berat Badan (kg)</label>
                            <input type="number"
                                x-model.number="bmiWeight"
                                min="30" max="200"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-stone-800">
                        </div>

                        {{-- Gender --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-700">Jenis Kelamin</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button"
                                    @click="bmiGender = 'l'"
                                    :class="bmiGender === 'l' ? 'bg-rose-50 text-rose-700 border-rose-300 font-bold' : 'bg-white text-stone-600 border-stone-200'"
                                    class="py-2 rounded-xl text-xs border transition-colors cursor-pointer text-center">
                                    Laki-laki
                                </button>
                                <button type="button"
                                    @click="bmiGender = 'p'"
                                    :class="bmiGender === 'p' ? 'bg-rose-50 text-rose-700 border-rose-300 font-bold' : 'bg-white text-stone-600 border-stone-200'"
                                    class="py-2 rounded-xl text-xs border transition-colors cursor-pointer text-center">
                                    Perempuan
                                </button>
                            </div>
                        </div>

                        {{-- Usia --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-stone-700">Usia (Tahun)</label>
                            <input type="number"
                                x-model.number="bmiAge"
                                min="10" max="100"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-stone-800">
                        </div>
                    </div>

                    {{-- Standar Rujukan Box --}}
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200/70 text-xs text-stone-600 space-y-1.5">
                        <div class="font-bold text-stone-800">Kategori Standar Kemenkes RI:</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-[11px]">
                            <div class="p-2 rounded-lg bg-white border border-stone-200/80">&lt; 18.5: Kurus</div>
                            <div class="p-2 rounded-lg bg-white border border-emerald-200 text-emerald-800 font-semibold">18.5 - 25.0: Normal</div>
                            <div class="p-2 rounded-lg bg-white border border-stone-200/80">25.1 - 27.0: Gemuk</div>
                            <div class="p-2 rounded-lg bg-white border border-stone-200/80">&gt; 27.0: Obesitas</div>
                        </div>
                    </div>
                </div>

                {{-- Hasil Perhitungan --}}
                <div class="lg:col-span-5 rounded-2xl p-6 bg-gradient-to-br from-white via-rose-50/40 to-red-50/30 border border-rose-100/90 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Hasil BMI Anda</span>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-4xl font-extrabold text-stone-900 tracking-tight" x-text="bmiValue"></span>
                            <span class="text-xs text-stone-500">kg/m²</span>
                        </div>

                        <div class="mt-3">
                            <span :class="[bmiCategory.bg, bmiCategory.color, bmiCategory.border]"
                                class="inline-block px-3 py-1 rounded-full text-xs font-bold border"
                                x-text="bmiCategory.label">
                            </span>
                        </div>

                        <p class="text-xs text-stone-600 mt-4 leading-relaxed" x-text="bmiCategory.desc"></p>

                        <div class="mt-6 pt-4 border-t border-rose-100/70 space-y-2 text-xs">
                            <div class="flex justify-between text-stone-600">
                                <span>Rentang Berat Ideal Anda:</span>
                                <span class="font-bold text-stone-800" x-text="idealWeightRange"></span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Rekomendasi Air Harian:</span>
                                <span class="font-bold text-stone-800">~ 2.2 - 2.5 Liter</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-[11px] text-stone-400 italic">
                        *Perhitungan ini merupakan perkiraan skrining gizi umum dan tidak menggantikan anjuran medis profesional.
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         6. MODUL 4: STREAK USER & KONSISTENSI
         ============================================================ --}}
    <section x-show="activeSection === 'streak'" x-cloak class="space-y-6">
        <div class="glass-card rounded-2xl p-6 sm:p-8 border border-rose-100/70 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-6 border-b border-rose-100/60">
                <div>
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-100">
                        Konsistensi Harian
                    </span>
                    <h3 class="text-xl font-bold text-stone-900 tracking-tight mt-2">Pelacak Streak Aktivitas</h3>
                    <p class="text-xs sm:text-sm text-stone-500 mt-1 leading-relaxed">
                        Bangun rutinitas sehatmu setiap hari. Lakukan aktivitas fisik minimal 20 menit untuk menjaga streak kebugaran.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button"
                        @click="toggleTodayCheckIn()"
                        :class="todayCheckedIn ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-gradient-to-r from-rose-500 to-red-500 text-white hover:from-rose-600 hover:to-red-600'"
                        class="px-5 py-2.5 rounded-xl font-semibold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                        <span x-show="!todayCheckedIn">🔥 Check-in Latihan Hari Ini</span>
                        <span x-show="todayCheckedIn">✓ Sudah Check-in Hari Ini!</span>
                    </button>
                </div>
            </div>

            {{-- 7-Day Calendar Strip --}}
            <div class="mt-6">
                <div class="text-xs font-bold text-stone-700 mb-3">Pekan Ini:</div>
                <div class="grid grid-cols-7 gap-2 sm:gap-3 text-center">
                    @php
                        $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    @endphp
                    @foreach($days as $index => $d)
                        <div class="p-3 sm:p-4 rounded-xl border {{ $index < 4 ? 'bg-rose-50/60 border-rose-200' : ($index == 4 ? 'bg-white border-rose-400 ring-2 ring-rose-200' : 'bg-stone-50 border-stone-200/60') }} flex flex-col items-center justify-center">
                            <span class="text-[11px] font-semibold text-stone-500">{{ $d }}</span>
                            <span class="text-base sm:text-lg mt-1 font-bold {{ $index < 4 ? 'text-rose-600' : ($index == 4 ? 'text-stone-900' : 'text-stone-400') }}">
                                {{ $index < 4 ? '✓' : ($index == 4 ? 'Hari Ini' : '-') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 p-4 rounded-xl bg-gradient-to-r from-white via-rose-50/40 to-red-50/20 border border-rose-100 text-xs text-stone-600 leading-relaxed">
                <span class="font-bold text-stone-900">Tips Konsistensi:</span> Membangun kebiasaan olahraga tidak memerlukan latihan yang terlalu berat setiap hari. Cukup jalan cepat, peregangan dinamis, atau joging ringan 20-30 menit untuk menjaga sirkulasi darah tetap prima.
            </div>
        </div>
    </section>

    {{-- ============================================================
         7. MODUL 5: MENU EDUKASI KESEHATAN
         ============================================================ --}}
    <section x-show="activeSection === 'edukasi'" x-cloak class="space-y-6">
        <div class="glass-card rounded-2xl p-5 sm:p-6 border border-rose-100/70 shadow-xs">
            <h3 class="text-lg font-bold text-stone-900 tracking-tight">Edukasi &amp; Tips Olahraga</h3>
            <p class="text-xs text-stone-500 mt-0.5">Panduan praktis menjaga performa tubuh, hidrasi, dan pemulihan dari praktisi olahraga</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Artikel 1 --}}
            <div class="glass-card rounded-2xl p-6 border border-rose-100/70 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                        Tips Latihan
                    </span>
                    <h4 class="text-base font-bold text-stone-900 mt-3 leading-snug">
                        Pentingnya Pemanasan Dinamis Sebelum Berolahraga
                    </h4>
                    <p class="text-xs text-stone-600 mt-2 leading-relaxed">
                        Pemanasan dinamis membantu menaikkan suhu inti otot dan melumasi sendi untuk mencegah risiko cedera mendadak saat sesi berintensitas tinggi.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-rose-50 flex items-center justify-between text-[11px] text-stone-400">
                    <span>⏱ 3 Menit Baca</span>
                    <span class="text-rose-600 font-semibold cursor-pointer hover:underline">Baca Selengkapnya &rarr;</span>
                </div>
            </div>

            {{-- Artikel 2 --}}
            <div class="glass-card rounded-2xl p-6 border border-rose-100/70 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Nutrisi &amp; Hidrasi
                    </span>
                    <h4 class="text-base font-bold text-stone-900 mt-3 leading-snug">
                        Panduan Hidrasi yang Tepat untuk Iklim Tropis
                    </h4>
                    <p class="text-xs text-stone-600 mt-2 leading-relaxed">
                        Keringat berlebih di Indonesia mempercepat kehilangan elektrolit. Minum 250ml air setiap 20 menit berolahraga untuk menjaga fokus dan stamina.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-rose-50 flex items-center justify-between text-[11px] text-stone-400">
                    <span>⏱ 4 Menit Baca</span>
                    <span class="text-rose-600 font-semibold cursor-pointer hover:underline">Baca Selengkapnya &rarr;</span>
                </div>
            </div>

            {{-- Artikel 3 --}}
            <div class="glass-card rounded-2xl p-6 border border-rose-100/70 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                        Pemulihan Otot
                    </span>
                    <h4 class="text-base font-bold text-stone-900 mt-3 leading-snug">
                        Tidur dan Manajemen Rest Day Berkualitas
                    </h4>
                    <p class="text-xs text-stone-600 mt-2 leading-relaxed">
                        Otot bertumbuh dan memperbaiki seratnya saat beristirahat. Pastikan tidur 7-8 jam per malam untuk menjaga performa konsisten jangka panjang.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-rose-50 flex items-center justify-between text-[11px] text-stone-400">
                    <span>⏱ 5 Menit Baca</span>
                    <span class="text-rose-600 font-semibold cursor-pointer hover:underline">Baca Selengkapnya &rarr;</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         8. MODUL 6: RIWAYAT AKTIVITAS USER
         ============================================================ --}}
    <section x-show="activeSection === 'riwayat'" x-cloak class="space-y-6">
        <div class="glass-card rounded-2xl p-5 sm:p-6 border border-rose-100/70 shadow-xs">
            <h3 class="text-lg font-bold text-stone-900 tracking-tight">Riwayat Aktivitas &amp; Keanggotaan</h3>
            <p class="text-xs text-stone-500 mt-0.5">Daftar komunitas yang kamu ikuti serta rekap sesi latihan aktif</p>
        </div>

        @if($userMemberships->isEmpty())
            <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h4 class="text-base font-bold text-stone-900 mb-1">Belum Bergabung dengan Komunitas</h4>
                <p class="text-xs text-stone-500 leading-relaxed mb-6">
                    Kamu belum terdaftar di komunitas manapun. Temukan klub lari, basket, atau badminton favoritmu dan mulai berolahraga bersama.
                </p>
                <button type="button"
                    @click="setSection('komunitas')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-colors cursor-pointer">
                    Cari Komunitas Sekarang
                </button>
            </div>
        @else
            <div class="glass-card rounded-2xl overflow-hidden border border-rose-100/70 shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-rose-50/50 border-b border-rose-100/80 text-stone-700 font-bold">
                                <th class="py-3.5 px-5">Komunitas</th>
                                <th class="py-3.5 px-5">Cabor</th>
                                <th class="py-3.5 px-5">Kota</th>
                                <th class="py-3.5 px-5">Status Keanggotaan</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-50">
                            @foreach($userMemberships as $m)
                                <tr class="hover:bg-rose-50/30 transition-colors">
                                    <td class="py-3 px-5 font-bold text-stone-900">
                                        {{ $m->community?->name ?? 'Komunitas' }}
                                    </td>
                                    <td class="py-3 px-5 text-stone-600">
                                        {{ ucfirst($m->community?->sport_category ?? '-') }}
                                    </td>
                                    <td class="py-3 px-5 text-stone-600">
                                        {{ $m->community?->city ?? '-' }}
                                    </td>
                                    <td class="py-3 px-5">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ ucfirst($m->status ?? 'Aktif') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-right">
                                        <button type="button" class="text-rose-600 hover:text-rose-700 font-semibold text-xs">
                                            Buka Ruang &rarr;
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

</div>
@endsection