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
            return { label: 'Obesitas', color: 'text-rose-700', bg: 'bg-rose-50', border: 'border-rose-200', desc: 'Konsultasikan dengan dokter atau pelatih untuk program latihan bertahap.' };
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
            <div class="relative overflow-hidden rounded-2xl border border-rose-200/70 bg-gradient-to-r from-white via-rose-50/70 to-red-50/50 p-6 sm:p-8 shadow-xs">
                <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-rose-600"><span aria-hidden="true">▦</span> Agenda Olahraga Terjadwal</span>
                        <h3 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-stone-900">Jadwal &amp; Agenda Olahraga</h3>
                        <p class="mt-2 max-w-2xl text-sm sm:text-base text-stone-600">Temukan sesi latihan rutin, fun match, dan turnamen dari komunitas yang kamu ikuti.</p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="sr-only" for="agenda-community-filter">Filter komunitas</label>
                        <select id="agenda-community-filter" x-model="agendaCommunityFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-stone-700">
                            <option value="all">Semua Komunitas</option>
                            <template x-for="community in agendaCommunities" :key="community"><option :value="community" x-text="community"></option></template>
                        </select>
                        <label class="sr-only" for="agenda-type-filter">Filter jenis kegiatan</label>
                        <select id="agenda-type-filter" x-model="agendaTypeFilter" class="glass-input rounded-xl px-4 py-3 text-sm text-stone-700">
                            <option value="all">Semua Jenis Kegiatan</option>
                            <option value="latihan_rutin">Latihan Rutin</option>
                            <option value="fun_match">Fun Match</option>
                            <option value="tournament">Turnamen</option>
                            <option value="gathering">Gathering</option>
                            <option value="workshop">Workshop</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <div class="flex rounded-xl border border-rose-100 bg-white/80 p-1 sm:col-span-2">
                            <button type="button" @click="agendaView = 'calendar'" :class="agendaView === 'calendar' ? 'bg-rose-50 text-rose-700 shadow-xs' : 'text-stone-500'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">▦ Kalender Bulan</button>
                            <button type="button" @click="agendaView = 'list'" :class="agendaView === 'list' ? 'bg-rose-50 text-rose-700 shadow-xs' : 'text-stone-500'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">☷ Daftar List</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.9fr)_minmax(320px,0.95fr)]">
                <div x-show="agendaView === 'calendar'" class="glass-card rounded-2xl p-4 sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h4 class="text-xl sm:text-2xl font-extrabold text-stone-900" x-text="agendaMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></h4>
                            <div class="flex items-center gap-1 rounded-xl bg-rose-50 p-1">
                                <button type="button" @click="agendaShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-rose-700 hover:bg-white" aria-label="Bulan sebelumnya">‹</button>
                                <button type="button" @click="agendaMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1); agendaSelectDate(new Date())" class="rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-rose-700">Hari Ini</button>
                                <button type="button" @click="agendaShiftMonth(1)" class="rounded-lg px-2.5 py-1.5 text-rose-700 hover:bg-white" aria-label="Bulan berikutnya">›</button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-stone-600">
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-rose-700">● Latihan Rutin</span>
                            <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-700">● Fun Match</span>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">● Turnamen</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-stone-400" x-text="day"></div></template>
                        <template x-for="date in agendaCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="agendaSelectDate(date)" :class="[
                                date.getMonth() === agendaMonth.getMonth() ? 'bg-rose-50/70 text-stone-700' : 'bg-stone-50/50 text-stone-300',
                                agendaSelectedDate === agendaDateKey(date) ? 'ring-2 ring-rose-500 bg-white' : 'hover:bg-rose-100/70',
                                agendaDateKey(date) === agendaDateKey(new Date()) ? 'font-extrabold' : ''
                            ].join(' ')" class="min-h-20 sm:min-h-24 rounded-xl p-1.5 sm:p-2 text-left transition-colors">
                                <span class="text-xs" x-text="date.getDate()"></span>
                                <div class="mt-1 space-y-1">
                                    <template x-for="item in agendaForDate(date).slice(0, 2)" :key="item.id">
                                        <span :class="item.type === 'latihan_rutin' ? 'bg-rose-200/80 text-rose-800' : (item.type === 'fun_match' ? 'bg-cyan-200/80 text-cyan-800' : 'bg-amber-200/80 text-amber-800')" class="block truncate rounded-md px-1 py-0.5 text-[9px] font-semibold" x-text="`${item.start} ${item.title}`"></span>
                                    </template>
                                    <span x-show="agendaForDate(date).length > 2" class="block text-[9px] text-stone-500" x-text="`+${agendaForDate(date).length - 2} agenda`"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="agendaView === 'list'" class="glass-card rounded-2xl p-5 sm:p-6">
                    <h4 class="text-xl font-extrabold text-stone-900">Agenda Mendatang</h4>
                    <p class="mb-4 mt-1 text-xs text-stone-500">Jadwal dalam 60 hari ke depan</p>
                    <div x-show="agendaUpcoming.length === 0" class="rounded-xl bg-rose-50/70 p-6 text-center text-sm text-stone-500">Belum ada agenda olahraga yang sesuai filter.</div>
                    <div class="max-h-[680px] space-y-3 overflow-y-auto pr-1">
                        <template x-for="item in agendaUpcoming" :key="`${item.id}-${item.occurrenceDate}`">
                            <button type="button" @click="agendaSelectedDate = item.occurrenceDate; agendaSelectedId = item.id; agendaMonth = new Date(`${item.occurrenceDate}T00:00:00`); agendaView = 'calendar'" class="w-full rounded-xl border border-rose-100 bg-white/80 p-4 text-left transition hover:border-rose-300 hover:shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-rose-600" x-text="new Date(`${item.occurrenceDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })"></span>
                                <span class="mt-1 block font-bold text-stone-900" x-text="item.title"></span>
                                <span class="mt-1 block text-xs text-stone-500" x-text="`${item.start} WIB · ${item.community}`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <template x-if="agendaSelectedItem">
                            <div>
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <span :class="agendaSelectedItem.type === 'latihan_rutin' ? 'bg-rose-100 text-rose-700' : 'bg-cyan-100 text-cyan-700'" class="rounded-full px-3 py-1 text-[10px] font-bold" x-text="agendaSelectedItem.type === 'latihan_rutin' ? 'Latihan Rutin' : agendaSelectedItem.type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"></span>
                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-[10px] font-bold text-stone-600" x-text="agendaSelectedItem.sport"></span>
                                </div>
                                <h4 class="text-2xl font-extrabold leading-tight text-stone-900" x-text="agendaSelectedItem.title"></h4>
                                <p class="mt-2 text-sm text-stone-500" x-text="agendaSelectedItem.description || `Kegiatan olahraga bersama ${agendaSelectedItem.community}.`"></p>
                                <div class="mt-5 space-y-4 rounded-xl bg-rose-50/80 p-4">
                                    <div><span class="block text-[10px] font-bold uppercase text-stone-400">Waktu &amp; Tanggal</span><strong class="mt-1 block text-sm text-stone-800" x-text="agendaSelectedItem.kind === 'event' ? new Date(`${agendaSelectedItem.date}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : `${['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][agendaSelectedItem.day]}, ${agendaSelectedItem.start} WIB`"></strong><span class="text-xs text-stone-500" x-text="`${agendaSelectedItem.start}${agendaSelectedItem.end ? ` – ${agendaSelectedItem.end}` : ''} WIB`"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-stone-400">Titik Kumpul</span><strong class="mt-1 block text-sm text-stone-800" x-text="agendaSelectedItem.location || 'Lokasi belum ditentukan'"></strong><span class="text-xs text-stone-500" x-text="agendaSelectedItem.community"></span></div>
                                    <div><span class="block text-[10px] font-bold uppercase text-stone-400">Kapasitas Slot</span><strong class="mt-1 block text-sm text-stone-800" x-text="agendaSelectedItem.capacity ? `${agendaSelectedItem.filled}/${agendaSelectedItem.capacity} Peserta` : 'Tanpa batas peserta'"></strong></div>
                                </div>
                                <a x-show="agendaSelectedItem.maps_url" :href="agendaSelectedItem.maps_url" target="_blank" rel="noopener noreferrer" class="mt-5 block w-full rounded-xl bg-gradient-to-r from-rose-500 to-red-500 px-4 py-3 text-center text-sm font-bold text-white shadow-sm">Buka Peta Lokasi <span aria-hidden="true">→</span></a>
                            </div>
                        </template>
                        <div x-show="!agendaSelectedItem" class="py-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-xl text-rose-500">▦</div>
                            <h4 class="font-bold text-stone-800">Pilih tanggal agenda</h4>
                            <p class="mt-1 text-xs text-stone-500">Pilih tanggal pada kalender untuk melihat detail kegiatan.</p>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-5">
                        <h4 class="font-bold text-stone-900">Agenda olahraga</h4>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs"><span class="rounded-full bg-rose-50 px-3 py-1.5 text-rose-700">{{ $schedules->count() }} latihan rutin</span><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-cyan-700">{{ $events->count() }} event</span></div>
                    </div>
                </aside>
            </div>
        </section>

        {{-- ============================================================
             6. LEADERBOARD USER OVERALL
             ============================================================ --}}
        <section x-show="activeSection === 'leaderboard'" x-cloak class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-rose-200/70 bg-gradient-to-r from-white via-rose-50/70 to-red-50/50 p-6 sm:p-8 shadow-xs">
                <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex rounded-full border border-rose-200 bg-rose-100/70 px-3 py-1 text-xs font-bold uppercase tracking-wide text-rose-700">Papan Peringkat Bugar.in</span>
                        <h3 class="mt-3 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">Leaderboard User Overall</h3>
                        <p class="mt-2 max-w-2xl text-sm text-stone-600">Peringkat pengguna berdasarkan poin yang tercatat, sesi olahraga yang dihadiri, dan streak aktivitas.</p>
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-white/80 p-1 text-xs font-semibold text-stone-600">
                        <span class="inline-flex items-center gap-2 px-3 py-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Keseluruhan pengguna aktif</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="glass-card rounded-2xl p-5"><p class="text-xs font-bold uppercase tracking-wide text-stone-500">Pengguna Aktif</p><p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($leaderboardStats['users'], 0, ',', '.') }}</p><p class="mt-1 text-xs text-stone-500">Akun pengguna terdaftar</p></div>
                <div class="glass-card rounded-2xl p-5"><p class="text-xs font-bold uppercase tracking-wide text-stone-500">Total Poin</p><p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($leaderboardStats['points'], 0, ',', '.') }}</p><p class="mt-1 text-xs text-stone-500">Poin yang tercatat pada akun</p></div>
                <div class="glass-card rounded-2xl p-5"><p class="text-xs font-bold uppercase tracking-wide text-stone-500">Sesi Dihadiri</p><p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($leaderboardStats['sessions'], 0, ',', '.') }}</p><p class="mt-1 text-xs text-stone-500">Kehadiran kegiatan komunitas</p></div>
                <div class="glass-card rounded-2xl p-5"><p class="text-xs font-bold uppercase tracking-wide text-stone-500">Streak Aktif</p><p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($leaderboardStats['active_streaks'], 0, ',', '.') }}</p><p class="mt-1 text-xs text-stone-500">Pengguna dengan streak berjalan</p></div>
            </div>

            <div class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div><span class="text-[11px] font-bold uppercase tracking-wide text-rose-600">Podium Kehormatan</span><h4 class="mt-1 text-2xl font-extrabold text-stone-900">Top 3 Overall</h4></div>
                    <p class="text-xs text-stone-500">Peringkat utama mengikuti total poin</p>
                </div>
                <div x-show="leaderboardTopThree.length === 0" class="glass-card rounded-2xl p-8 text-center text-sm text-stone-500">Belum ada pengguna aktif untuk ditampilkan.</div>
                <div x-show="leaderboardTopThree.length > 0" class="grid items-end gap-4 lg:grid-cols-3">
                    <template x-for="person in leaderboardTopThree" :key="person.id">
                        <article :class="person.rank === 1 ? 'lg:order-2 lg:-translate-y-2 border-rose-200 shadow-sm' : (person.rank === 2 ? 'lg:order-1' : 'lg:order-3')" class="glass-card rounded-2xl border p-5 text-center transition-transform">
                            <span :class="person.rank === 1 ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700'" class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold" x-text="`RANK #${person.rank}`"></span>
                            <div class="mx-auto mt-4 flex h-16 w-16 items-center justify-center rounded-full border-4 border-rose-200 bg-gradient-to-br from-rose-500 to-red-500 text-xl font-extrabold text-white" x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()"></div>
                            <h5 class="mt-3 text-lg font-extrabold text-stone-900" x-text="person.name"></h5>
                            <p class="text-xs text-stone-500" x-text="person.username ? `@${person.username}` : 'Pengguna Bugar.in'"></p>
                            <div class="mt-4 grid grid-cols-3 divide-x divide-rose-100 rounded-xl bg-rose-50/80 py-3">
                                <div><strong class="block text-lg font-extrabold text-rose-700" x-text="Number(person.total_points).toLocaleString('id-ID')"></strong><span class="text-[10px] text-stone-500">Poin</span></div>
                                <div><strong class="block text-lg font-extrabold text-rose-700" x-text="person.attended_sessions_count"></strong><span class="text-[10px] text-stone-500">Sesi</span></div>
                                <div><strong class="block text-lg font-extrabold text-rose-700" x-text="`${person.longest_streak}h`"></strong><span class="text-[10px] text-stone-500">Streak</span></div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-5 sm:p-7">
                <div class="flex flex-col gap-4 border-b border-rose-100 pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div><h4 class="text-xl font-extrabold text-stone-900">Peringkat Lengkap Anggota</h4><p class="mt-1 text-xs text-stone-500">Menampilkan hingga 15 pengguna dengan peringkat teratas</p></div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="leaderboardMetric = 'points'" :class="leaderboardMetric === 'points' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'" class="rounded-full px-3 py-2 text-[11px] font-bold">Total Poin</button>
                        <button type="button" @click="leaderboardMetric = 'sessions'" :class="leaderboardMetric === 'sessions' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'" class="rounded-full px-3 py-2 text-[11px] font-bold">Sesi Kehadiran</button>
                        <button type="button" @click="leaderboardMetric = 'streak'" :class="leaderboardMetric === 'streak' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'" class="rounded-full px-3 py-2 text-[11px] font-bold">Streak Terpanjang</button>
                    </div>
                </div>

                <div x-show="leaderboardCurrentUser" class="my-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-gradient-to-r from-rose-600 to-red-500 p-4 text-white shadow-xs">
                    <div class="flex items-center gap-3"><span class="rounded-lg bg-white/20 px-3 py-2 text-xl font-extrabold" x-text="`#${leaderboardCurrentUser?.rank ?? '—'}`"></span><div><strong class="block text-sm" x-text="leaderboardCurrentUser?.name"></strong><span class="text-xs text-white/80">Posisimu di leaderboard</span></div></div>
                    <span class="text-sm font-bold" x-text="`${Number(leaderboardCurrentUser?.total_points ?? 0).toLocaleString('id-ID')} poin`"></span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead><tr class="bg-rose-50 text-[10px] font-bold uppercase tracking-wide text-rose-700"><th class="rounded-l-xl px-4 py-3">Peringkat</th><th class="px-4 py-3">Pengguna</th><th class="px-4 py-3 text-right">Total Poin</th><th class="px-4 py-3 text-right">Sesi Hadir</th><th class="rounded-r-xl px-4 py-3 text-right">Streak Terbaik</th></tr></thead>
                        <tbody>
                            <template x-for="person in leaderboardRows.slice(0, 15)" :key="person.id">
                                <tr :class="person.is_current_user ? 'bg-rose-50/70' : 'hover:bg-stone-50/80'" class="border-b border-rose-50 last:border-0">
                                    <td class="px-4 py-3.5 font-extrabold text-rose-600" x-text="String(person.rank).padStart(2, '0')"></td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-rose-100 text-xs font-extrabold text-rose-700" x-text="person.name.split(' ').map(part => part[0]).slice(0, 2).join('').toUpperCase()"></span><div><strong class="block text-sm text-stone-800" x-text="person.name"></strong><span class="text-[11px] text-stone-500" x-text="person.username ? `@${person.username}` : 'Pengguna'" ></span></div><span x-show="person.is_current_user" class="rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-bold text-white">Kamu</span></div></td>
                                    <td class="px-4 py-3.5 text-right font-bold text-stone-800" x-text="Number(person.total_points).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-3.5 text-right text-stone-700" x-text="person.attended_sessions_count"></td>
                                    <td class="px-4 py-3.5 text-right"><span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700" x-text="`${person.longest_streak} hari`"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="leaderboardRows.length === 0" class="p-8 text-center text-sm text-stone-500">Belum ada data peringkat.</div>
                </div>
                <p class="mt-5 rounded-xl bg-rose-50/80 p-4 text-xs leading-relaxed text-stone-600">Peringkat dihitung dari poin pengguna yang tersimpan. Jumlah sesi dan streak berasal dari data kehadiran serta aktivitas komunitas yang sudah tercatat.</p>
            </div>
        </section>

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
                    <button type="button"
                        @click="setSection('komunitas')"
                        class="px-5 py-2.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 text-white shadow-xs hover:shadow-sm transition-all duration-150 flex items-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        Jelajah Komunitas
                    </button>

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

                {{-- Filter Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4">
                    {{-- Search Input --}}
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <input type="text"
                            x-model="searchQuery"
                            placeholder="Cari nama komunitas..."
                            class="glass-input w-full pl-9 pr-3.5 py-2 text-xs rounded-xl text-stone-800 placeholder-stone-400">
                    </div>

                    {{-- Cabor Filter --}}
                    <div class="relative">
                        <select x-model="filterCategory" class="glass-input w-full px-3.5 py-2 text-xs rounded-xl text-stone-800 cursor-pointer">
                            <option value="">Semua Cabang Olahraga</option>
                            @foreach($sportCategories as $cat)
                                <option value="{{ strtolower($cat) }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lokasi / Kota Filter --}}
                    <div class="relative">
                        <select x-model="filterCity" class="glass-input w-full px-3.5 py-2 text-xs rounded-xl text-stone-800 cursor-pointer">
                            <option value="">Semua Lokasi / Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ strtolower($city) }}">{{ ucfirst($city) }}</option>
                            @endforeach
                        </select>
                    </div>
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
                                        <span>👤 {{ $comm->followers_count ?? 0 }} Pengikut</span>
                                    </div>
                                </div>

                                {{-- Card Footer --}}
                                <div class="mt-5 pt-3 flex items-center justify-between">
                                    <span class="text-[11px] text-stone-400">
                                        {{ $comm->contact_email ?? 'Email terdaftar' }}
                                    </span>
                                    @if(in_array($comm->id, $followedCommunityIds, true))
                                        <form method="POST" action="{{ route('user.communities.unfollow', $comm) }}">
                                            @csrf @method('DELETE')
                                            <button class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-rose-700 border border-rose-200">Mengikuti · Batal</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('user.communities.follow', $comm) }}">
                                            @csrf
                                            <button class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">Ikuti komunitas</button>
                                        </form>
                                    @endif
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
                                    <form method="POST" action="{{ route('user.schedules.join', $sch) }}">@csrf<button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-colors">Ikut Sesi</button></form>
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
                                <form method="POST" action="{{ route('user.events.join', $event) }}">@csrf<button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition-colors">Daftar Event</button></form>
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
                        <div class="flex items-center gap-1 rounded-xl bg-rose-50 p-1">
                            <button type="button" @click="streakShiftMonth(-1)" class="rounded-lg px-2.5 py-1.5 text-rose-700 hover:bg-white" aria-label="Bulan sebelumnya">‹</button>
                            <span class="min-w-32 px-2 text-center text-xs font-bold text-rose-700" x-text="streakMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></span>
                            <button type="button" @click="streakShiftMonth(1)" :disabled="streakMonth.getFullYear() === new Date().getFullYear() && streakMonth.getMonth() >= new Date().getMonth()" class="rounded-lg px-2.5 py-1.5 text-rose-700 hover:bg-white disabled:opacity-40" aria-label="Bulan berikutnya">›</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day"><div class="py-2 text-center text-[10px] font-bold uppercase tracking-wide text-stone-400" x-text="day"></div></template>
                        <template x-for="date in streakCalendarCells()" :key="agendaDateKey(date)">
                            <button type="button" @click="streakSelectedDate = agendaDateKey(date)" :class="[
                                date.getMonth() === streakMonth.getMonth() ? 'bg-rose-50/70 text-stone-700' : 'bg-stone-50/50 text-stone-300',
                                streakSelectedDate === agendaDateKey(date) ? 'ring-2 ring-rose-500 bg-white' : 'hover:bg-rose-100/70',
                                streakActivityOn(date) ? 'border border-emerald-300' : 'border border-transparent'
                            ].join(' ')" class="relative min-h-16 rounded-xl p-2 text-left transition-colors sm:min-h-20">
                                <span class="text-xs font-semibold" x-text="date.getDate()"></span>
                                <span x-show="streakActivityOn(date)" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-emerald-500" aria-label="Ada kehadiran terverifikasi"></span>
                                <span x-show="agendaDateKey(date) === agendaDateKey(new Date())" class="mt-2 block text-[9px] font-bold text-rose-600">Hari ini</span>
                            </button>
                        </template>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-[11px] text-stone-500"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Kehadiran terverifikasi</div>
                </div>

                <div class="space-y-5">
                    <div class="glass-card rounded-2xl p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-bold uppercase tracking-wide text-rose-600">Aktivitas Terverifikasi</span><h4 class="mt-1 text-lg font-extrabold text-stone-900" x-text="new Date(`${streakSelectedDate}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })"></h4></div><span class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700" x-text="`${streakActivitiesOnSelectedDate().length} sesi`"></span></div>
                        <div x-show="streakActivitiesOnSelectedDate().length === 0" class="mt-4 rounded-xl bg-stone-50 p-4 text-xs leading-relaxed text-stone-500">Belum ada kehadiran terverifikasi pada tanggal ini. Kehadiran dicatat oleh penyelenggara kegiatan.</div>
                        <div class="mt-4 space-y-3">
                            <template x-for="activity in streakActivitiesOnSelectedDate()" :key="activity.id">
                                <div class="rounded-xl border border-rose-100 bg-white/80 p-4">
                                    <div class="flex items-start justify-between gap-2"><strong class="text-sm text-stone-900" x-text="activity.title"></strong><span class="text-xs font-bold text-rose-600" x-text="activity.time ? `${activity.time} WIB` : ''"></span></div>
                                    <p class="mt-1 text-xs text-stone-500" x-text="activity.community"></p>
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700" x-text="activity.method === 'qr_scan' ? 'Verifikasi QR' : (activity.method === 'auto' ? 'Verifikasi otomatis' : 'Verifikasi manual')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="glass-card rounded-2xl p-5">
                        <div class="flex items-center justify-between"><h4 class="font-bold text-stone-900">Konsistensi Pekan Ini</h4><strong class="text-sm text-rose-600">{{ $streakStats['active_days_this_week'] }} hari aktif</strong></div>
                        <p class="mt-2 text-xs leading-relaxed text-stone-500">Jumlah hari dalam pekan ini dengan kehadiran terverifikasi. Hadiri sesi komunitas untuk menjaga streak.</p>
                        @if($streakRecords->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($streakRecords as $streak)
                                    <span class="rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-semibold text-rose-700">{{ ucfirst($streak->sport_category) }} · {{ $streak->current_streak }} hari</span>
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
                    <div class="mb-4 flex items-end justify-between gap-3"><div><h4 class="text-lg font-extrabold text-stone-900">Riwayat Aktivitas Terverifikasi</h4><p class="mt-1 text-xs text-stone-500">Sesi yang tercatat pada tabel kehadiran</p></div><span class="rounded-full bg-rose-50 px-3 py-1 text-[10px] font-bold text-rose-700">{{ $streakStats['sessions'] }} sesi</span></div>
                    @if($verifiedActivities->isEmpty())
                        <div class="rounded-xl bg-stone-50 p-6 text-center text-xs text-stone-500">Belum ada aktivitas dengan kehadiran terverifikasi.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($verifiedActivities->take(5) as $activity)
                                <div class="rounded-xl border border-rose-100 bg-white/80 p-4">
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
                                <div class="flex items-start justify-between gap-4 rounded-xl border border-rose-100 bg-white/80 p-4">
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