@extends('layouts.user')

@section('title', 'Dashboard Pengguna - Bugar.in')

@section('content')
    <div x-data="{
        filterCategory: '',
        filterCity: '',
        searchQuery: '',
        komunitasSubTab: 'komunitas', // 'komunitas' | 'jadwal'
        agendaItems: @js($agendaItems),
        agendaView: 'calendar',
        agendaTypeFilter: 'all',
        agendaCommunityFilter: 'all',
        agendaMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        agendaSelectedDate: new Date().toLocaleDateString('sv-SE'),
        agendaSelectedId: null,

        agendaDateKey(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        agendaCalendarCells() {
            const first = new Date(this.agendaMonth.getFullYear(), this.agendaMonth.getMonth(), 1);
            const start = new Date(first);
            start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return date;
            });
        },
        agendaMatchesFilters(item) {
            return (this.agendaTypeFilter === 'all' || item.type === this.agendaTypeFilter) &&
                (this.agendaCommunityFilter === 'all' || item.community === this.agendaCommunityFilter);
        },
        agendaForDate(date) {
            const key = this.agendaDateKey(date);
            return this.agendaItems.filter(item => this.agendaMatchesFilters(item) &&
                (item.date === key || (item.kind === 'schedule' && item.day === date.getDay())));
        },
        get agendaSelectedItem() {
            return this.agendaItems.find(item => item.id === this.agendaSelectedId && this.agendaMatchesFilters(item)) ||
                this.agendaForDate(new Date(`${this.agendaSelectedDate}T00:00:00`))[0] || null;
        },
        agendaSelectDate(date) {
            this.agendaSelectedDate = this.agendaDateKey(date);
            const firstItem = this.agendaForDate(date)[0];
            this.agendaSelectedId = firstItem ? firstItem.id : null;
        },
        agendaShiftMonth(amount) {
            this.agendaMonth = new Date(this.agendaMonth.getFullYear(), this.agendaMonth.getMonth() + amount, 1);
        },
        get agendaCommunities() {
            return [...new Set(this.agendaItems.map(item => item.community))];
        },
        get agendaUpcoming() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const upcoming = [];
            for (let offset = 0; offset < 60; offset++) {
                const date = new Date(today);
                date.setDate(today.getDate() + offset);
                this.agendaForDate(date).forEach(item => upcoming.push({ ...item, occurrenceDate: this.agendaDateKey(date) }));
            }
            return upcoming;
        },
        leaderboardMetric: 'points',
        leaderboardUsers: @js($leaderboardUsers->map(fn($rankedUser) => [
            'id' => $rankedUser->id,
            'name' => $rankedUser->name,
            'username' => $rankedUser->username,
            'total_points' => (int) $rankedUser->total_points,
            'attended_sessions_count' => (int) $rankedUser->attended_sessions_count,
            'current_streak' => (int) ($rankedUser->current_streak ?? 0),
            'longest_streak' => (int) ($rankedUser->longest_streak ?? 0),
            'is_current_user' => $rankedUser->is_current_user,
        ])->values()),
        get leaderboardRows() {
            const metric = { points: 'total_points', sessions: 'attended_sessions_count', streak: 'longest_streak' }[this.leaderboardMetric];
            return [...this.leaderboardUsers]
                .sort((a, b) => (b[metric] - a[metric]) || (b.total_points - a.total_points) || a.name.localeCompare(b.name, 'id'))
                .map((person, index) => ({ ...person, rank: index + 1 }));
        },
        get leaderboardTopThree() { return this.leaderboardRows.slice(0, 3); },
        get leaderboardCurrentUser() { return this.leaderboardRows.find(person => person.is_current_user) || null; },

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
            return { label: 'Obesitas', color: 'text-red-700', bg: 'bg-red-50', border: 'border-red-200', desc: 'Konsultasikan dengan dokter atau pelatih untuk program latihan bertahap.' };
        },

        get idealWeightRange() {
            if (!this.bmiHeight) return '-';
            let h = this.bmiHeight / 100;
            let min = (18.5 * h * h).toFixed(1);
            let max = (25.0 * h * h).toFixed(1);
            return `${min} kg - ${max} kg`;
        },

        streakMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        streakSelectedDate: new Date().toLocaleDateString('sv-SE'),
        streakActivityDates: @js($streakActivityDates),
        streakActivities: @js($verifiedActivities),
        streakCalendarCells() {
            const first = new Date(this.streakMonth.getFullYear(), this.streakMonth.getMonth(), 1);
            const start = new Date(first);
            start.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                return date;
            });
        },
        streakActivityOn(date) {
            return this.streakActivityDates.includes(this.agendaDateKey(date));
        },
        streakActivitiesOnSelectedDate() {
            return this.streakActivities.filter(activity => activity.date === this.streakSelectedDate);
        },
        streakShiftMonth(amount) {
            this.streakMonth = new Date(this.streakMonth.getFullYear(), this.streakMonth.getMonth() + amount, 1);
        }
    }" class="space-y-8 w-full max-w-7xl">

        @if(session('status') || session('streak_error') || session('participation_error'))
            <div role="status" class="rounded-xl {{ session('streak_error') || session('participation_error') ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-700' }} p-4 text-sm font-semibold">
                {{ session('status') ?? session('streak_error') ?? session('participation_error') }}
            </div>
        @endif

        {{-- ============================================================
             5. MODUL: KALENDER JADWAL & AGENDA OLAHRAGA
             ============================================================ --}}
        <section x-show="activeSection === 'agenda'" x-cloak class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-blue-200/80 bg-gradient-to-r from-white via-blue-50/70 to-cyan-50/40 p-6 sm:p-8 shadow-xs">
                <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-600"><span aria-hidden="true">▦</span> Agenda Olahraga Terjadwal</span>
                        <h3 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Jadwal &amp; Agenda Olahraga</h3>
                        <p class="mt-2 max-w-2xl text-sm sm:text-base text-slate-600">Temukan sesi latihan rutin, fun match, dan turnamen dari komunitas yang kamu ikuti.</p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="sr-only" for="agenda-community-filter">Filter komunitas</label>
                        <select id="agenda-community-filter" x-model="agendaCommunityFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-slate-700">
                            <option value="all">Semua Komunitas</option>
                            <template x-for="community in agendaCommunities" :key="community"><option :value="community" x-text="community"></option></template>
                        </select>
                        <label class="sr-only" for="agenda-type-filter">Filter jenis kegiatan</label>
                        <select id="agenda-type-filter" x-model="agendaTypeFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-slate-700">
                            <option value="all">Semua Jenis Kegiatan</option>
                            <option value="latihan_rutin">Latihan Rutin</option>
                            <option value="fun_match">Fun Match</option>
                            <option value="tournament">Turnamen</option>
                            <option value="gathering">Gathering</option>
                            <option value="workshop">Workshop</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <div class="flex rounded-xl border border-blue-100 bg-white/90 p-1 sm:col-span-2 shadow-2xs">
                            <button type="button" @click="agendaView = 'calendar'" :class="agendaView === 'calendar' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition-all">▦ Kalender Bulan</button>
                            <button type="button" @click="agendaView = 'list'" :class="agendaView === 'list' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition-all">☷ Daftar List</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.9fr)_minmax(320px,0.95fr)]">
                <div x-show="agendaView === 'calendar'" class="glass-card rounded-2xl p-4 sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h4 class="text-xl sm:text-2xl font-extrabold text-slate-900" x-text="agendaMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></h4>
                            <div class="flex items-center gap-1 rounded-xl bg-blue-50/80 p-1 border border-blue-100">
                                <button type="button" @click="agendaShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan sebelumnya">‹</button>
                                <button type="button" @click="agendaMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1); agendaSelectDate(new Date())" class="rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-blue-700 shadow-2xs">Hari Ini</button>
                                <button type="button" @click="agendaShiftMonth(1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan berikutnya">›</button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-slate-600">
                            <span class="rounded-full bg-blue-50 border border-blue-100 px-2.5 py-1 text-blue-700">● Latihan Rutin</span>
                            <span class="rounded-full bg-cyan-50 border border-cyan-100 px-2.5 py-1 text-cyan-700">● Fun Match</span>
                            <span class="rounded-full bg-amber-50 border border-amber-100 px-2.5 py-1 text-amber-700">● Turnamen</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-slate-400" x-text="day"></div></template>
                        <template x-for="date in agendaCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="agendaSelectDate(date)" :class="[
                                date.getMonth() === agendaMonth.getMonth() ? 'bg-blue-50/40 text-slate-700' : 'bg-slate-50/50 text-slate-300',
                                agendaSelectedDate === agendaDateKey(date) ? 'ring-2 ring-blue-500 bg-white shadow-xs' : 'hover:bg-blue-100/50',
                                agendaDateKey(date) === agendaDateKey(new Date()) ? 'font-extrabold border border-blue-300' : 'border border-slate-100'
                            ].join(' ')" class="min-h-20 sm:min-h-24 rounded-xl p-1.5 sm:p-2 text-left transition-colors">
                                <span class="text-xs" x-text="date.getDate()"></span>
                                <div class="mt-1 space-y-1">
                                    <template x-for="item in agendaForDate(date).slice(0, 2)" :key="item.id">
                                        <span :class="item.type === 'latihan_rutin' ? 'bg-blue-100/90 text-blue-800' : (item.type === 'fun_match' ? 'bg-cyan-100/90 text-cyan-800' : 'bg-amber-100/90 text-amber-800')" class="block truncate rounded-md px-1 py-0.5 text-[9px] font-semibold" x-text="`${item.start} ${item.title}`"></span>
                                    </template>
                                    <span x-show="agendaForDate(date).length > 2" class="block text-[9px] text-slate-500" x-text="`+${agendaForDate(date).length - 2} agenda`"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="agendaView === 'list'" class="glass-card rounded-2xl p-5 sm:p-6">
                    <h4 class="text-xl font-extrabold text-slate-900">Agenda Mendatang</h4>
                    <p class="mb-4 mt-1 text-xs text-slate-500">Jadwal dalam 60 hari ke depan</p>
                    <div x-show="agendaUpcoming.length === 0" class="rounded-xl bg-blue-50/50 border border-blue-100 p-6 text-center text-sm text-slate-500">Belum ada agenda olahraga yang sesuai filter.</div>
                    <div class="max-h-[680px] space-y-3 overflow-y-auto pr-1">
                        <template x-for="item in agendaUpcoming" :key="`${item.id}-${item.occurrenceDate}`">
                            <button type="button" @click="agendaSelectedDate = item.occurrenceDate; agendaSelectedId = item.id; agendaMonth = new Date(`${item.occurrenceDate}T00:00:00`); agendaView = 'calendar'" class="w-full rounded-xl border border-slate-100 bg-white/90 p-4 text-left transition hover:border-blue-300 hover:shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-blue-600" x-text="new Date(`${item.occurrenceDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })"></span>
                                <span class="mt-1 block font-bold text-slate-900" x-text="item.title"></span>
                                <span class="mt-1 block text-xs text-slate-500" x-text="`${item.start} WIB · ${item.community}`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <template x-if="agendaSelectedItem">
                            <div>
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <span :class="agendaSelectedItem.type === 'latihan_rutin' ? 'bg-blue-100 text-blue-700' : 'bg-cyan-100 text-cyan-700'" class="rounded-full px-3 py-1 text-[10px] font-bold" x-text="agendaSelectedItem.type === 'latihan_rutin' ? 'Latihan Rutin' : agendaSelectedItem.type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"></span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600" x-text="agendaSelectedItem.sport"></span>
                                </div>
                                <h4 class="text-2xl font-extrabold leading-tight text-slate-900" x-text="agendaSelectedItem.title"></h4>
                                <p class="mt-2 text-sm text-slate-500" x-text="agendaSelectedItem.description || `Kegiatan olahraga bersama ${agendaSelectedItem.community}.`"></p>
                                <div class="mt-5 space-y-4 rounded-xl bg-blue-50/70 border border-blue-100 p-4">
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Waktu &amp; Tanggal</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.kind === 'event' ? new Date(`${agendaSelectedItem.date}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : `${['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][agendaSelectedItem.day]}, ${agendaSelectedItem.start} WIB`"></strong><span class="text-xs text-slate-500" x-text="`${agendaSelectedItem.start}${agendaSelectedItem.end ? ` – ${agendaSelectedItem.end}` : ''} WIB`"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Titik Kumpul</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.location || 'Lokasi belum ditentukan'"></strong><span class="text-xs text-slate-500" x-text="agendaSelectedItem.community"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-slate-400">Kapasitas Slot</span><strong class="mt-1 block text-sm text-slate-800" x-text="agendaSelectedItem.capacity ? `${agendaSelectedItem.filled}/${agendaSelectedItem.capacity} Peserta` : 'Tanpa batas peserta'"></strong></div>
                                </div>
                                <a x-show="agendaSelectedItem.maps_url" :href="agendaSelectedItem.maps_url" target="_blank" rel="noopener noreferrer" class="mt-5 block w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 px-4 py-3 text-center text-sm font-bold text-white shadow-md shadow-blue-500/20 transition-all">Buka Peta Lokasi <span aria-hidden="true">→</span></a>
                            </div>
                        </template>
                        <div x-show="!agendaSelectedItem" class="py-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl text-blue-600 border border-blue-100">▦</div>
                            <h4 class="font-bold text-slate-800">Pilih tanggal agenda</h4>
                            <p class="mt-1 text-xs text-slate-500">Pilih tanggal pada kalender untuk melihat detail kegiatan.</p>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-5">
                        <h4 class="font-bold text-slate-900">Agenda olahraga</h4>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs"><span class="rounded-full bg-blue-50 border border-blue-100 px-3 py-1.5 text-blue-700 font-semibold">{{ $schedules->count() }} latihan rutin</span><span class="rounded-full bg-cyan-50 border border-cyan-100 px-3 py-1.5 text-cyan-700 font-semibold">{{ $events->count() }} event</span></div>
                    </div>
                </aside>
            </div>
        </section>

        {{-- ============================================================
             6. LEADERBOARD USER OVERALL
             ============================================================ --}}
        <section x-show="activeSection === 'leaderboard'" x-cloak class="space-y-6">
            {{-- Section banner --}}
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-white via-blue-50/40 to-sky-50/30 p-6 sm:p-8 shadow-xs">
                <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-gradient-to-br from-blue-200/20 to-transparent blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-700">
                            <span class="active-dot"></span>Papan Peringkat Bugar.in
                        </span>
                        <h3 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Leaderboard User Overall</h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">Peringkat pengguna berdasarkan poin yang tercatat, sesi olahraga yang dihadiri, dan streak aktivitas.</p>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-white/90 px-4 py-3 text-xs font-semibold text-slate-600">
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Keseluruhan pengguna aktif</span>
                    </div>
                </div>
            </div>

            {{-- Stats row — editorial, variasi warna --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengguna Aktif</p><div class="tile-icon tile-icon--blue"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['users'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Akun pengguna terdaftar</p>
                </div>
                <div class="activity-tile activity-tile--hero">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Poin</p><div class="tile-icon tile-icon--white"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-white tabular-nums">{{ number_format($leaderboardStats['points'], 0, ',', '.') }}</p>
                    <p class="text-xs text-white/60">Poin yang tercatat</p>
                </div>
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sesi Dihadiri</p><div class="tile-icon tile-icon--cyan"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['sessions'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Kehadiran kegiatan komunitas</p>
                </div>
                <div class="activity-tile">
                    <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Streak Aktif</p><div class="tile-icon tile-icon--green"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg></div></div>
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($leaderboardStats['active_streaks'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Pengguna dengan streak berjalan</p>
                </div>
            </div>

            {{-- Top 3 Podium --}}
            <div class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wide text-blue-600">Podium Kehormatan</span>
                        <h4 class="mt-1 text-2xl font-extrabold text-slate-900">Top 3 Overall</h4>
                    </div>
                    <p class="text-xs text-slate-500">Peringkat utama mengikuti total poin</p>
                </div>
                <div x-show="leaderboardTopThree.length === 0" class="glass-card rounded-2xl p-8 text-center text-sm text-slate-500">Belum ada pengguna aktif untuk ditampilkan.</div>
                <div x-show="leaderboardTopThree.length > 0" class="grid items-end gap-4 lg:grid-cols-3">
                    <template x-for="person in leaderboardTopThree" :key="person.id">
                        <article
                            :class="person.rank === 1
                                ? 'lg:order-2 lg:-translate-y-3 border-yellow-300 shadow-lg shadow-yellow-100'
                                : person.rank === 2
                                    ? 'lg:order-1 border-slate-300'
                                    : 'lg:order-3 border-orange-300'"
                            class="glass-card rounded-2xl border-2 p-5 text-center transition-all duration-200 hover:-translate-y-1">
                            <span
                                :class="person.rank === 1 ? 'bg-yellow-400 text-yellow-900' : person.rank === 2 ? 'bg-slate-200 text-slate-700' : 'bg-orange-200 text-orange-800'"
                                class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold"
                                x-text="`RANK #${person.rank}`">
                            </span>
                            <div
                                :class="person.rank === 1
                                    ? 'border-yellow-300 bg-gradient-to-br from-yellow-400 to-orange-400'
                                    : 'border-blue-200 bg-gradient-to-br from-blue-600 to-cyan-500'"
                                class="mx-auto mt-4 flex h-16 w-16 items-center justify-center rounded-full border-4 text-xl font-extrabold text-white"
                                x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()">
                            </div>
                            <h5 class="mt-3 text-base font-extrabold text-slate-900" x-text="person.name"></h5>
                            <p class="text-xs text-slate-400" x-text="person.username ? `@${person.username}` : 'Pengguna Bugar.in'"></p>
                            <div class="mt-4 grid grid-cols-3 divide-x divide-blue-100 rounded-xl bg-blue-50/60 py-3">
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="Number(person.total_points).toLocaleString('id-ID')"></strong><span class="text-[10px] text-slate-400">Poin</span></div>
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="person.attended_sessions_count"></strong><span class="text-[10px] text-slate-400">Sesi</span></div>
                                <div><strong class="block text-base font-extrabold text-blue-700" x-text="`${person.longest_streak}h`"></strong><span class="text-[10px] text-slate-400">Streak</span></div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            {{-- Full Rank Table --}}
            <div class="glass-card rounded-2xl p-5 sm:p-7">
                <div class="flex flex-col gap-4 border-b border-blue-100 pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div><h4 class="text-xl font-extrabold text-slate-900">Peringkat Lengkap Anggota</h4><p class="mt-1 text-xs text-slate-500">Menampilkan hingga 15 pengguna dengan peringkat teratas</p></div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="leaderboardMetric = 'points'" :class="leaderboardMetric === 'points' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Total Poin</button>
                        <button type="button" @click="leaderboardMetric = 'sessions'" :class="leaderboardMetric === 'sessions' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Sesi Kehadiran</button>
                        <button type="button" @click="leaderboardMetric = 'streak'" :class="leaderboardMetric === 'streak' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" class="rounded-full px-3 py-2 text-[11px] font-bold transition-colors">Streak Terpanjang</button>
                    </div>
                </div>

                <div x-show="leaderboardCurrentUser" class="my-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 p-4 text-white shadow-sm">
                    <div class="flex items-center gap-3"><span class="rounded-lg bg-white/20 px-3 py-2 text-xl font-extrabold" x-text="`#${leaderboardCurrentUser?.rank ?? '—'}`"></span><div><strong class="block text-sm" x-text="leaderboardCurrentUser?.name"></strong><span class="text-xs text-white/80">Posisimu di leaderboard</span></div></div>
                    <span class="text-sm font-bold" x-text="`${Number(leaderboardCurrentUser?.total_points ?? 0).toLocaleString('id-ID')} poin`"></span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead><tr class="bg-blue-50 text-[10px] font-bold uppercase tracking-wide text-blue-700"><th class="rounded-l-xl px-4 py-3">Peringkat</th><th class="px-4 py-3">Pengguna</th><th class="px-4 py-3 text-right">Total Poin</th><th class="px-4 py-3 text-right">Sesi Hadir</th><th class="rounded-r-xl px-4 py-3 text-right">Streak Terbaik</th></tr></thead>
                        <tbody>
                            <template x-for="person in leaderboardRows.slice(0, 15)" :key="person.id">
                                <tr :class="person.is_current_user ? 'bg-blue-50/60' : 'hover:bg-slate-50/80'" class="border-b border-slate-100 last:border-0">
                                    <td class="px-4 py-3.5 font-extrabold text-blue-600" x-text="String(person.rank).padStart(2, '0')"></td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-extrabold text-white" x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()"></span><div><strong class="block text-sm text-slate-800" x-text="person.name"></strong><span class="text-[11px] text-slate-400" x-text="person.username ? `@${person.username}` : 'Pengguna'"></span></div><span x-show="person.is_current_user" class="rounded bg-blue-600 px-1.5 py-0.5 text-[9px] font-bold text-white">Kamu</span></div></td>
                                    <td class="px-4 py-3.5 text-right font-bold text-slate-800" x-text="Number(person.total_points).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-3.5 text-right text-slate-600" x-text="person.attended_sessions_count"></td>
                                    <td class="px-4 py-3.5 text-right"><span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700" x-text="`${person.longest_streak} hari`"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="leaderboardRows.length === 0" class="p-8 text-center text-sm text-slate-500">Belum ada data peringkat.</div>
                </div>
                <p class="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-xs leading-relaxed text-slate-600">Peringkat dihitung dari poin pengguna yang tersimpan. Jumlah sesi dan streak berasal dari data kehadiran serta aktivitas komunitas yang sudah tercatat.</p>
            </div>
        </section>



        {{-- ============================================================
             3. DASHBOARD UTAMA & KOMUNITAS TERBAIK
             ============================================================ --}}
        <section x-show="activeSection === 'komunitas'" x-cloak class="space-y-8">

            {{-- 1. Athlete Personal Header Strip --}}
            <div class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-gradient-to-br from-white via-blue-50/50 to-sky-50/40 p-6 sm:p-8 shadow-xs">
                <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-300/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/70 border border-blue-200 text-blue-700 text-xs font-bold mb-3 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Dashboard Aktivitas Olahraga</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Halo, {{ $user->name ?? 'Pegiat Olahraga' }}! 👋
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl leading-relaxed">
                            Pantau keaktifan komunitasmu, ikuti sesi latihan rutin pekan ini, atau temukan klub olahraga terbaik di sekitarmu.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 bg-white/90 border border-blue-100 rounded-2xl px-3.5 py-2.5 shadow-2xs">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">⚡</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Poin Gerak</span>
                                <strong class="text-xs font-bold text-slate-800">{{ number_format($user->total_points ?? 0, 0, ',', '.') }} PTS</strong>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-white/90 border border-blue-100 rounded-2xl px-3.5 py-2.5 shadow-2xs">
                            <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-bold text-sm">🔥</span>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400">Streak</span>
                                <strong class="text-xs font-bold text-slate-800">{{ $streakStats['current'] ?? 0 }} Hari</strong>
                            </div>
                        </div>
                        <a href="{{ route('user.explore') }}" class="btn-primary text-xs font-bold py-2.5 px-4 rounded-xl flex items-center gap-1.5 shadow-xs">
                            <span>Eksplor Semua Komunitas</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. Komunitas Terbaik & Terpopuler Showcase --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>🏆 Komunitas Terbaik &amp; Terpopuler</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilihan klub teratas berdasarkan status verifikasi dan jumlah anggota terbanyak</p>
                    </div>
                    <a href="{{ route('user.explore') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 group">
                        <span>Buka Direktori ({{ $totalCommunitiesCount }})</span>
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>

                @if($topCommunities->isEmpty())
                    <div class="glass-card rounded-2xl p-10 text-center max-w-md mx-auto">
                        <p class="text-sm font-semibold text-slate-700">Belum ada komunitas terdaftar saat ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($topCommunities as $comm)
                            @php
                                $commCategory = strtolower($comm->sport_category ?? '');
                                $sportCoverClass = match($commCategory) {
                                    'futsal'    => 'sport-cover-futsal',
                                    'basket', 'basketball' => 'sport-cover-basket',
                                    'badminton' => 'sport-cover-badminton',
                                    'lari', 'running' => 'sport-cover-lari',
                                    'renang', 'swimming' => 'sport-cover-renang',
                                    'yoga'      => 'sport-cover-yoga',
                                    default     => 'sport-cover-default',
                                };
                            @endphp
                            <div class="community-card flex flex-col h-full bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                                {{-- Cover Image --}}
                                <a href="{{ route('user.communities.show', $comm->id) }}" class="community-card__cover {{ $sportCoverClass }} relative h-40 overflow-hidden rounded-t-2xl block group cursor-pointer">
                                    @if($comm->cover_image)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($comm->cover_image, ['http://', 'https://']) ? $comm->cover_image : \Illuminate\Support\Facades\Storage::url($comm->cover_image) }}"
                                             alt="{{ $comm->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <div class="community-card__overlay"></div>
                                    <span class="community-card__badge">{{ ucfirst($comm->sport_category ?? 'Olahraga') }}</span>
                                    @if($comm->verification_status === 'verified')
                                        <span class="absolute top-3 right-3 bg-blue-600/95 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                                            <svg class="w-3 h-3 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Terverifikasi
                                        </span>
                                    @endif
                                </a>

                                <div class="community-card__body p-5 flex flex-col flex-grow justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                                <a href="{{ route('user.communities.show', $comm->id) }}" class="hover:text-blue-600 transition-colors">
                                                    {{ $comm->name }}
                                                </a>
                                            </h4>
                                            @if($comm->city)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $comm->city }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                            {{ $comm->description ?? 'Komunitas olahraga aktif yang terbuka untuk anggota baru dan sesi latihan rutin bersama.' }}
                                        </p>
                                    </div>

                                    {{-- Meta: Members, Sesi, Event --}}
                                    <div class="community-card__meta mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $comm->members_count ?? 0 }} anggota
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $comm->schedules_count ?? 0 }} sesi
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $comm->events_count ?? 0 }} event
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Footer --}}
                                <div class="community-card__footer p-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-between">
                                    <span class="text-[11px] font-medium text-slate-400">{{ $comm->followers_count ?? 0 }} pengikut</span>
                                    @if(in_array($comm->id, $followedCommunityIds, true))
                                        <form method="POST" action="{{ route('user.communities.unfollow', $comm) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-outline text-[11px] py-1.5 px-3 rounded-lg">✓ Mengikuti</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('user.communities.follow', $comm) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary text-[11px] py-1.5 px-3 rounded-lg">Ikuti Klub</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 3. Agenda & Event Olahraga Mendatang --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Column 1: Event Mendatang --}}
                <div class="glass-card rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>📅 Event Olahraga Mendatang</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Kompetisi, turnamen, dan fun gathering terbuka</p>
                            </div>
                            <button type="button" @click="setSection('events')" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                Semua Event &rarr;
                            </button>
                        </div>

                        @if($events->isEmpty())
                            <div class="py-8 text-center text-xs text-slate-400">Belum ada event olahraga mendatang.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($events->take(2) as $ev)
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-blue-200 transition-all flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">
                                                {{ $ev->event_date ? \Carbon\Carbon::parse($ev->event_date)->translatedFormat('d M Y') : 'Mendatang' }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ $ev->title }}</h4>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                {{ $ev->community?->name ?? 'Komunitas' }} &bull; {{ $ev->location ?? 'Lokasi via Maps' }}
                                            </p>
                                        </div>
                                        <button type="button" @click="setSection('events')" class="flex-shrink-0 text-[11px] font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100 cursor-pointer">
                                            Detail
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Column 2: Jadwal Rutin Terdekat --}}
                <div class="glass-card rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>⏰ Sesi Latihan Rutin Komunitas</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Jadwal latihan mingguan bersama rekan komunitas</p>
                            </div>
                            <button type="button" @click="setSection('agenda')" class="text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                Buka Kalender &rarr;
                            </button>
                        </div>

                        @if($schedules->isEmpty())
                            <div class="py-8 text-center text-xs text-slate-400">Belum ada jadwal latihan rutin yang diterbitkan.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($schedules->take(2) as $sch)
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-blue-200 transition-all flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-cyan-600 uppercase tracking-wider block">
                                                {{ $sch->dayLabel() }} &bull; {{ substr($sch->start_time, 0, 5) }}–{{ substr($sch->end_time, 0, 5) }} WIB
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ $sch->title }}</h4>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                {{ $sch->community?->name ?? 'Komunitas' }} &bull; {{ $sch->location ?? 'Lokasi belum ditentukan' }}
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('user.schedules.join', $sch) }}" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="btn-primary text-[11px] py-1.5 px-3 rounded-lg">Ikut</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Pintasan Cepat Fitur --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div @click="setSection('agenda')" class="glass-card rounded-2xl p-5 border border-slate-200/80 shadow-xs cursor-pointer hover:border-blue-300 transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 border border-blue-100">
                        ▦
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Agenda Kalender Latihan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Cek agenda latihan harian, sesi fun match, dan jadwal turnamen.</p>
                    </div>
                </div>
                <div @click="setSection('bmi')" class="glass-card rounded-2xl p-5 border border-slate-200/80 shadow-xs cursor-pointer hover:border-emerald-300 transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0 border border-emerald-100">
                        ⚖️
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Kalkulator Body Mass Index (BMI)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Hitung status massa tubuh ideal dan dapatkan tips kebugaran.</p>
                    </div>
                </div>
            </div>

        </section>

        {{-- ============================================================
             4. MODUL 2: EKSPLORASI EVENT
             ============================================================ --}}
        <section x-show="activeSection === 'events'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-orange-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Eksplorasi Event Mendatang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Turnamen, fun run, gathering olahraga, dan kompetisi yang akan diselenggarakan</p>
                </div>
                <span class="badge badge--orange self-start sm:self-auto">
                    {{ $events->count() }} Event Tersedia
                </span>
            </div>

            @if($events->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto mb-4 border border-orange-100 shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1.5">Belum Ada Event Mendatang</h4>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                        Komunitas belum mempublikasikan event olahraga baru saat ini. Seluruh pengumuman kompetisi dan gathering akan otomatis tampil di sini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($events as $event)
                        <div class="event-card">
                            {{-- Cover area dengan orange gradient --}}
                            <div class="event-card__cover">
                                @if($event->cover_image ?? false)
                                    <img src="{{ \Illuminate\Support\Str::startsWith($event->cover_image, ['http://', 'https://']) ? $event->cover_image : \Illuminate\Support\Facades\Storage::url($event->cover_image) }}"
                                         alt="{{ $event->title }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                @endif
                                <span class="event-card__type-badge">{{ $event->community?->name ?? 'Event' }}</span>
                            </div>
                            <div class="p-5 flex flex-col flex-1 gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="badge badge--orange text-[10px]">{{ $event->community?->sport_category ?? 'Event' }}</span>
                                        {{ $event->community?->name ?? 'Komunitas' }}
                                    </span>
                                    <span class="text-xs font-bold {{ $event->registration_fee > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                        {{ $event->registration_fee > 0 ? 'Rp ' . number_format($event->registration_fee, 0, ',', '.') : 'Gratis' }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $event->title }}</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $event->description ?? 'Event olahraga terbuka untuk peserta umum.' }}</p>

                                <div class="mt-3 space-y-1.5 text-xs text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="truncate">{{ $event->location ?? 'Lokasi belum ditentukan' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kuota: {{ $event->capacity ?? 'Bebas' }} peserta</span>
                                <form method="POST" action="{{ route('user.events.join', $event) }}">@csrf<button type="submit" class="btn-primary text-[11px] py-1.5 px-3">Daftar Event</button></form>
                            </div>
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
            <div class="glass-card rounded-2xl p-6 sm:p-8 border border-blue-100 shadow-xs">
                <div class="max-w-2xl mb-8">
                    <span class="badge badge--green mb-2">Kesehatan &amp; Kebugaran</span>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight mt-2">Kalkulator Body Mass Index (BMI)</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
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
                                        :class="bmiGender === 'l' ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold shadow-2xs' : 'bg-white text-stone-600 border-stone-200'"
                                        class="py-2.5 rounded-xl text-xs border transition-all cursor-pointer text-center">
                                        Laki-laki
                                    </button>
                                    <button type="button"
                                        @click="bmiGender = 'p'"
                                        :class="bmiGender === 'p' ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold shadow-2xs' : 'bg-white text-stone-600 border-stone-200'"
                                        class="py-2.5 rounded-xl text-xs border transition-all cursor-pointer text-center">
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
                    <div class="lg:col-span-5 rounded-2xl p-6 bg-gradient-to-br from-white via-blue-50/30 to-sky-50/20 border border-blue-100 shadow-xs flex flex-col justify-between">
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

                            <div class="mt-6 pt-4 border-t border-slate-100 space-y-2 text-xs">
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
             6. MODUL 4: STREAK & POIN USER
             ============================================================ --}}
        <section x-show="activeSection === 'streak'" x-cloak class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-white via-blue-50 to-cyan-50 p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-5">
                    <div>
                        <span class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cyan-800">Program Konsistensi Olahraga</span>
                        <h3 class="mt-3 text-3xl font-extrabold tracking-tight text-blue-700 sm:text-4xl">Streak &amp; Poin Olahraga</h3>
                        <p class="mt-2 max-w-3xl text-sm text-blue-700">Pantau rutinitas olahraga, kehadiran komunitas, dan Gerak Poin dari sesi yang tercatat.</p>
                        <p class="mt-2 text-xs font-semibold text-slate-500">Check-in hanya dapat dilakukan pada sesi terdaftar dari komunitas terverifikasi atau yang memiliki minimal 10 pengikut.</p>
                    </div>
                    <div class="min-w-52 rounded-2xl bg-white/90 p-4 shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Status Akun</span>
                        <strong class="mt-1 block text-sm text-blue-700">{{ $user->email_verified_at ? 'Terverifikasi Aktif' : 'Aktif' }}</strong>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Streak Aktif</p><p class="mt-3 text-3xl font-extrabold text-blue-600">{{ number_format($streakStats['current'], 0, ',', '.') }} <span class="text-base">hari</span></p><p class="mt-1 text-xs text-slate-500">Hari berturut-turut dengan kehadiran layak</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Gerak Poin</p><p class="mt-3 text-3xl font-extrabold text-cyan-600">{{ number_format($pointsStats['balance'], 0, ',', '.') }} <span class="text-base">PTS</span></p><p class="mt-1 text-xs text-slate-500">Saldo poin akunmu</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Sesi Terverifikasi</p><p class="mt-3 text-3xl font-extrabold text-blue-600">{{ number_format($streakStats['sessions'], 0, ',', '.') }} <span class="text-base">sesi</span></p><p class="mt-1 text-xs text-slate-500">Kehadiran dari komunitas yang memenuhi syarat</p></div>
                <div class="glass-card rounded-2xl border border-blue-100 p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rekor Streak</p><p class="mt-3 text-3xl font-extrabold text-cyan-600">{{ number_format($streakStats['longest'], 0, ',', '.') }} <span class="text-base">hari</span></p><p class="mt-1 text-xs text-slate-500">Rekor dari kalender kehadiran layak</p></div>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div><h4 class="text-lg font-extrabold text-blue-700">Ritme Olahraga Pekan Ini</h4><p class="mt-1 text-xs text-slate-500">{{ $streakStats['active_days_this_week'] }} dari 5 hari target berdasarkan kehadiran komunitas</p></div>
                    <form method="POST" action="{{ route('user.streak.check-in') }}">@csrf<button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-3 text-sm font-bold text-white shadow-sm hover:brightness-105">⚡ Check-in sesi hari ini · +30 Poin</button></form>
                </div>
                <div class="mt-5 grid grid-cols-4 gap-2 sm:grid-cols-7 sm:gap-3">
                    @foreach($weeklyStreakDays as $day)
                        <div class="rounded-xl {{ $day['completed'] ? 'bg-cyan-100 text-cyan-800' : ($day['is_today'] ? 'bg-blue-600 text-white' : 'bg-blue-50 text-slate-500') }} p-3 text-center">
                            <span class="block text-[10px] font-bold uppercase">{{ $day['label'] }}</span><span class="mx-auto mt-2 flex h-7 w-7 items-center justify-center rounded-full {{ $day['completed'] ? 'bg-cyan-500 text-white' : ($day['is_today'] ? 'bg-white/20 text-white' : 'bg-white text-slate-400') }}">{{ $day['completed'] ? '✓' : ($day['is_today'] ? '⚡' : '·') }}</span>
                            <span class="mt-1 block text-[10px]">{{ $day['completed'] ? 'Tercapai' : ($day['is_today'] ? 'Hari ini' : '—') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.85fr)]">
                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div><h4 class="text-xl font-extrabold text-stone-900">Kalender Konsistensi &amp; Streak Olahraga</h4><p class="mt-1 text-xs text-stone-500">Tanggal bertanda menunjukkan kehadiran yang terverifikasi</p></div>
                        <div class="flex items-center gap-1 rounded-xl bg-blue-50 p-1">
                            <button type="button" @click="streakShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white transition-colors" aria-label="Bulan sebelumnya">‹</button>
                            <span class="min-w-32 px-2 text-center text-xs font-bold text-blue-700" x-text="streakMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></span>
                            <button type="button" @click="streakShiftMonth(1)" :disabled="streakMonth.getFullYear() === new Date().getFullYear() && streakMonth.getMonth() >= new Date().getMonth()" class="rounded-lg px-2.5 py-1.5 text-blue-700 hover:bg-white disabled:opacity-40 transition-colors" aria-label="Bulan berikutnya">›</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-stone-400" x-text="day"></div></template>
                        <template x-for="date in streakCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="streakSelectedDate = agendaDateKey(date)" :class="[
                                date.getMonth() === streakMonth.getMonth() ? 'bg-blue-50/50 text-slate-700' : 'bg-slate-50/50 text-slate-300',
                                streakSelectedDate === agendaDateKey(date) ? 'ring-2 ring-blue-500 bg-white' : 'hover:bg-blue-100/50',
                                streakActivityOn(date) ? 'border border-emerald-300' : 'border border-transparent'
                            ].join(' ')" class="relative min-h-16 rounded-xl p-2 text-left transition-colors sm:min-h-20">
                                <span class="text-xs font-semibold" x-text="date.getDate()"></span>
                                <span x-show="streakActivityOn(date)" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-emerald-500" aria-label="Ada kehadiran terverifikasi"></span>
                                <span x-show="agendaDateKey(date) === agendaDateKey(new Date())" class="mt-2 block text-[9px] font-bold text-blue-600">Hari ini</span>
                            </button>
                        </template>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-[11px] text-stone-500"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Kehadiran terverifikasi</div>
                </div>

                <div class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-bold uppercase tracking-wide text-blue-600">Aktivitas Terverifikasi</span><h4 class="mt-1 text-lg font-extrabold text-slate-900" x-text="new Date(`${streakSelectedDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })"></h4></div><span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700" x-text="`${streakActivitiesOnSelectedDate().length} sesi`"></span></div>
                        <div x-show="streakActivitiesOnSelectedDate().length === 0" class="mt-4 rounded-xl bg-stone-50 p-4 text-xs leading-relaxed text-stone-500">Belum ada kehadiran terverifikasi pada tanggal ini. Kehadiran dicatat oleh penyelenggara kegiatan.</div>
                        <div class="mt-4 space-y-3">
                            <template x-for="activity in streakActivitiesOnSelectedDate()" :key="activity.id">
                                <div class="rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div class="flex items-start justify-between gap-2"><strong class="text-sm text-slate-900" x-text="activity.title"></strong><span class="text-xs font-bold text-blue-600" x-text="activity.time ? `${activity.time} WIB` : ''"></span></div>
                                    <p class="mt-1 text-xs text-stone-500" x-text="activity.community"></p>
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700" x-text="activity.method === 'qr_scan' ? 'Verifikasi QR' : (activity.method === 'auto' ? 'Verifikasi otomatis' : 'Verifikasi manual')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-5">
                        <div class="flex items-center justify-between"><h4 class="font-bold text-slate-900">Konsistensi Pekan Ini</h4><strong class="text-sm text-blue-600">{{ $streakStats['active_days_this_week'] }} hari aktif</strong></div>
                        <p class="mt-2 text-xs leading-relaxed text-stone-500">Jumlah hari dalam pekan ini dengan kehadiran terverifikasi. Hadiri sesi komunitas untuk menjaga streak.</p>
                        @if($streakRecords->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($streakRecords as $streak)
                                    <span class="badge badge--blue">{{ ucfirst($streak->sport_category) }} · {{ $streak->current_streak }} hari</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div><h4 class="text-xl font-extrabold text-blue-700">Target &amp; Milestone Konsistensi</h4><p class="mt-1 text-xs text-slate-500">Progres berdasarkan kehadiran sesi olahraga yang memenuhi syarat.</p></div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700">{{ $streakStats['current'] }} hari streak aktif</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach($streakMilestones as $milestone)
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <div class="flex items-center justify-between gap-3"><strong class="text-sm text-blue-800">{{ $milestone['target'] }} Hari Konsisten</strong><span class="text-xs font-bold text-cyan-700">{{ $milestone['completed'] ? 'Tercapai' : $milestone['progress'] . ' / ' . $milestone['target'] }}</span></div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-500" style="width: {{ $milestone['percentage'] }}%"></div></div>
                            <p class="mt-2 text-[11px] text-slate-500">{{ $milestone['completed'] ? 'Rekor streak-mu sudah mencapai milestone ini.' : 'Lanjutkan olahraga bersama komunitas untuk membuka milestone.' }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 rounded-xl border border-cyan-100 bg-cyan-50 p-4 text-xs leading-relaxed text-cyan-900">Bonus konsistensi mingguan sebesar 100 Gerak Poin diberikan satu kali saat kehadiranmu tercatat pada minimal 5 hari berbeda dalam satu pekan.</p>
            </div>

            <div class="glass-card rounded-2xl border border-blue-100 p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3"><div><h4 class="text-xl font-extrabold text-blue-700">Reward Mitra Olahraga</h4><p class="mt-1 text-xs text-slate-500">Tukar Gerak Poin dengan reward yang tersedia.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700">Saldo {{ number_format($pointsStats['balance'], 0, ',', '.') }} PTS</span></div>
                @if($pointRewards->isEmpty())
                    <p class="mt-5 rounded-xl bg-blue-50 p-5 text-sm text-slate-600">Belum ada reward tersedia. Katalog akan menampilkan reward setelah admin menambahkan mitra dan stoknya.</p>
                @else
                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($pointRewards as $reward)
                            <article class="flex flex-col justify-between rounded-2xl bg-blue-50 p-4">
                                <div><div class="flex items-start justify-between gap-3"><span class="rounded-lg bg-white p-2 text-xl text-blue-600">🎁</span><strong class="text-sm font-extrabold text-cyan-700">{{ number_format($reward->points_cost, 0, ',', '.') }} PTS</strong></div><h5 class="mt-3 font-bold text-blue-800">{{ $reward->name }}</h5><p class="mt-1 text-xs text-slate-500">{{ $reward->partner ?: 'Mitra Bugar.in' }}</p><p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $reward->description }}</p></div>
                                <div class="mt-4 flex items-center justify-between gap-3"><span class="text-[11px] text-slate-500">Stok {{ $reward->stock }}</span><form method="POST" action="{{ route('user.rewards.redeem', $reward) }}">@csrf<button type="submit" @disabled($pointsStats['balance'] < $reward->points_cost) class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">Tukar</button></form></div>
                            </article>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('user.dashboard', ['section' => 'streak']) }}" class="mt-5 inline-block text-xs font-bold text-blue-600">Riwayat poin &amp; penukaran ada di bawah halaman</a>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-3"><div><h4 class="text-lg font-extrabold text-slate-900">Riwayat Aktivitas Terverifikasi</h4><p class="mt-1 text-xs text-slate-500">Sesi yang tercatat pada tabel kehadiran</p></div><span class="badge badge--blue">{{ $streakStats['sessions'] }} sesi</span></div>
                    @if($verifiedActivities->isEmpty())
                        <div class="rounded-xl bg-stone-50 p-6 text-center text-xs text-stone-500">Belum ada aktivitas dengan kehadiran terverifikasi.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($verifiedActivities->take(5) as $activity)
                                <div class="rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-2"><strong class="text-sm text-stone-900">{{ $activity['title'] }}</strong><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">Terverifikasi</span></div>
                                    <p class="mt-1 text-xs text-stone-500">{{ $activity['community'] }}</p>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-stone-500"><span>{{ \Carbon\Carbon::parse($activity['date'])->translatedFormat('d F Y') }} · {{ $activity['time'] ?? '--:--' }} WIB</span><span>{{ $activity['method'] === 'qr_scan' ? 'QR' : ($activity['method'] === 'auto' ? 'Otomatis' : 'Manual') }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="glass-card rounded-2xl p-5 sm:p-6">
                    <div class="mb-4 flex items-end justify-between gap-3"><div><h4 class="text-lg font-extrabold text-stone-900">Riwayat Gerak Poin</h4><p class="mt-1 text-xs text-stone-500">Transaksi poin yang tersimpan pada akunmu</p></div><span class="rounded-full bg-cyan-50 px-3 py-1 text-[10px] font-bold text-cyan-700">{{ number_format($pointsStats['earned_this_month'], 0, ',', '.') }} poin bulan ini</span></div>
                    @if($pointsHistory->isEmpty())
                        <div class="rounded-xl bg-stone-50 p-6 text-center text-xs text-stone-500">Belum ada transaksi poin. Poin akan muncul setelah transaksi dicatat pada akunmu.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($pointsHistory as $point)
                                <div class="flex items-start justify-between gap-4 rounded-xl border border-blue-100 bg-white/80 p-4">
                                    <div><strong class="block text-sm text-stone-900">{{ $point->description ?: ucfirst(str_replace('_', ' ', $point->source)) }}</strong><span class="mt-1 block text-[11px] text-stone-500">{{ $point->created_at->translatedFormat('d F Y · H:i') }}</span><span class="mt-1 block text-[10px] text-stone-400">Saldo setelah transaksi: {{ number_format($point->balance_after, 0, ',', '.') }} poin</span></div>
                                    <div><strong class="block text-sm text-stone-900">{{ $point->description ?: ucfirst(str_replace('_', ' ', $point->source)) }}</strong><span class="mt-1 block text-[11px] text-stone-500">{{ $point->created_at->translatedFormat('d F Y · H:i') }}</span><span class="mt-1 block text-[10px] text-stone-400">Saldo setelah transaksi: {{ number_format($point->balance_after, 0, ',', '.') }} poin</span>@if($point->pointable instanceof \App\Models\PointRedemption)<span class="mt-1 block text-[10px] font-bold text-blue-700">Status penukaran: {{ ucfirst($point->pointable->status) }}</span>@endif</div>
                                    <span class="shrink-0 rounded-full {{ $point->points >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} px-2.5 py-1 text-xs font-extrabold">{{ $point->points > 0 ? '+' : '' }}{{ number_format($point->points, 0, ',', '.') }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ============================================================
             7. MODUL 5: MENU EDUKASI KESEHATAN
             ============================================================ --}}
        <section x-show="activeSection === 'edukasi'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-blue-100 shadow-xs">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Edukasi &amp; Tips Olahraga</h3>
                <p class="text-xs text-slate-500 mt-0.5">Panduan praktis menjaga performa tubuh, hidrasi, dan pemulihan dari praktisi olahraga</p>
            </div>

            {{-- Editorial 3-col: variasi ukuran dan warna kartu --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Artikel 1: Tips Latihan — Blue accent --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-blue-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-blue-600 to-blue-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--blue mb-3">Tips Latihan</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Pentingnya Pemanasan Dinamis Sebelum Berolahraga
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Pemanasan dinamis membantu menaikkan suhu inti otot dan melumasi sendi untuk mencegah risiko cedera mendadak saat sesi berintensitas tinggi.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                3 Menit Baca
                            </span>
                            <span class="text-blue-600 font-semibold cursor-pointer hover:underline">Baca &rarr;</span>
                        </div>
                    </div>
                </div>

                {{-- Artikel 2: Nutrisi — Emerald accent, slightly taller --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-emerald-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--green mb-3">Nutrisi &amp; Hidrasi</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Panduan Hidrasi yang Tepat untuk Iklim Tropis
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Keringat berlebih di Indonesia mempercepat kehilangan elektrolit. Minum 250ml air setiap 20 menit berolahraga untuk menjaga fokus dan stamina.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                4 Menit Baca
                            </span>
                            <span class="text-emerald-600 font-semibold cursor-pointer hover:underline">Baca &rarr;</span>
                        </div>
                    </div>
                </div>

                {{-- Artikel 3: Pemulihan — Orange accent --}}
                <div class="glass-card rounded-2xl overflow-hidden border border-orange-100 flex flex-col hover:-translate-y-1 transition-transform duration-200">
                    <div class="h-3 bg-gradient-to-r from-orange-400 to-amber-400"></div>
                    <div class="p-6 flex flex-col flex-1">
                        <span class="badge badge--orange mb-3">Pemulihan Otot</span>
                        <h4 class="text-base font-bold text-slate-900 leading-snug">
                            Tidur dan Manajemen Rest Day Berkualitas
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed flex-1">
                            Otot bertumbuh dan memperbaiki seratnya saat beristirahat. Pastikan tidur 7-8 jam per malam untuk menjaga performa konsisten jangka panjang.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                5 Menit Baca
                            </span>
                            <span class="text-orange-600 font-semibold cursor-pointer hover:underline">Baca &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             8. MODUL 6: RIWAYAT AKTIVITAS USER
             ============================================================ --}}
        <section x-show="activeSection === 'riwayat'" x-cloak class="space-y-6">
            <div class="glass-card rounded-2xl p-5 sm:p-6 border border-blue-100 shadow-xs">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Riwayat Aktivitas &amp; Keanggotaan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar komunitas yang kamu ikuti serta rekap sesi latihan aktif</p>
            </div>

            @if($userMemberships->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-4 border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Belum Bergabung dengan Komunitas</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Kamu belum terdaftar di komunitas manapun. Temukan klub lari, basket, atau badminton favoritmu dan mulai berolahraga bersama.
                    </p>
                    <button type="button" @click="setSection('komunitas')" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cari Komunitas Sekarang
                    </button>
                </div>
            @else
                <div class="glass-card rounded-2xl overflow-hidden border border-blue-100 shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-blue-50/60 border-b border-blue-100 text-slate-700 font-bold">
                                    <th class="py-3.5 px-5">Komunitas</th>
                                    <th class="py-3.5 px-5">Cabor</th>
                                    <th class="py-3.5 px-5">Kota</th>
                                    <th class="py-3.5 px-5">Status Keanggotaan</th>
                                    <th class="py-3.5 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($userMemberships as $m)
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="py-3 px-5 font-bold text-slate-900">
                                            {{ $m->community?->name ?? 'Komunitas' }}
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="badge badge--blue">{{ ucfirst($m->community?->sport_category ?? '-') }}</span>
                                        </td>
                                        <td class="py-3 px-5 text-slate-500">
                                            {{ $m->community?->city ?? '-' }}
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="badge badge--green badge--pulse">
                                                {{ ucfirst($m->status ?? 'Aktif') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <button type="button" @click="setSection('komunitas')" class="text-blue-600 hover:text-blue-800 font-semibold text-xs transition-colors">
                                                Lihat Detail &rarr;
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