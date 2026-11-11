@extends('layouts.penyedia-komunitas')

@section('title', 'Check-in Sesi: ' . $schedule->title . ' - Bugar.in')

@push('head')
{{-- CDN html5-qrcode versi persis 2.3.8 dengan fallback ke jsDelivr --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    if (typeof Html5Qrcode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
    }
</script>
<style>
    #reader video {
        border-radius: 0.75rem;
        object-fit: cover;
        width: 100% !important;
    }
    #reader {
        border: none !important;
    }
</style>
@endpush

@php
    $initialParticipantsData = $participants->map(function ($p) use ($attendances) {
        $att = $attendances->get($p->id);
        $tz = config('app.timezone', 'Asia/Jakarta');
        $avatarUrl = null;
        if ($p->user?->avatar) {
            $avatarUrl = \Illuminate\Support\Str::startsWith($p->user->avatar, ['http://', 'https://'])
                ? $p->user->avatar
                : \Illuminate\Support\Facades\Storage::url($p->user->avatar);
        }
        return [
            'id' => $p->id,
            'user_id' => $p->user_id,
            'user_name' => $p->user?->name ?? 'Peserta #' . $p->user_id,
            'user_email' => $p->user?->email ?? '-',
            'user_avatar' => $avatarUrl,
            'status' => $p->status,
            'status_label' => $p->statusLabel(),
            'status_color' => $p->statusColor(),
            'check_in_time' => $att && $att->check_in_at ? $att->check_in_at->timezone($tz)->format('H:i') : null,
            'method' => $att?->method,
        ];
    })->values();
@endphp

@section('content')
<div class="space-y-6"
     x-data="checkinScanner({
         scheduleId: {{ $schedule->id }},
         scanEndpoint: '{{ route('penyedia-komunitas.jadwal.checkin.scan', $schedule) }}',
         participantsEndpoint: '{{ route('penyedia-komunitas.jadwal.checkin.participants', $schedule) }}',
         manualCheckinBase: '{{ url('/penyedia-komunitas/jadwal/' . $schedule->id . '/checkin/manual') }}',
         csrfToken: '{{ csrf_token() }}',
         initialParticipants: @json($initialParticipantsData),
         initialCounts: {
             total: {{ $totalCount }},
             attended: {{ $attendedCount }},
             registered: {{ $registeredCount }}
         },
         isToday: {{ $isToday ? 'true' : 'false' }},
         isWindowOpen: {{ $isWindowOpen ? 'true' : 'false' }}
     })"
     x-init="initScanner()">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('penyedia-komunitas.jadwal.index') }}" class="hover:text-blue-600 transition">Jadwal</a>
                <span>›</span>
                <a href="{{ route('penyedia-komunitas.jadwal.show', $schedule) }}" class="hover:text-blue-600 transition truncate max-w-[200px]">{{ $schedule->title }}</a>
                <span>›</span>
                <span class="text-blue-700 font-bold">Check-in QR</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <span>Check-in Sesi: {{ $schedule->title }}</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $schedule->levelBadgeClass() }}">
                    {{ $schedule->levelLabel() }}
                </span>
                @if($schedule->streak_enabled)
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        ⚡ Streak Aktif
                    </span>
                @endif
            </h1>
            <p class="mt-1 text-sm text-slate-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span>📅 {{ $schedule->date ? $schedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : $schedule->dayLabel() }}</span>
                <span>⏰ {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB</span>
                <span>📍 {{ $schedule->location }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('penyedia-komunitas.jadwal.show', $schedule) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                <span>← Kembali ke Detail</span>
            </a>
        </div>
    </div>

    {{-- Status Banner Sesi --}}
    @if(!$isToday)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 flex items-start gap-3">
            <span class="text-xl">⚠️</span>
            <div>
                <strong class="font-bold block">Sesi Tidak Dijadwalkan Hari Ini</strong>
                <span>Sesi ini dijadwalkan pada <strong>{{ $schedule->date ? $schedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}</strong>. Check-in hanya dapat diproses pada hari sesi berlangsung.</span>
            </div>
        </div>
    @elseif(!$isWindowOpen)
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 flex items-start gap-3">
            <span class="text-xl">ℹ️</span>
            <div>
                <strong class="font-bold block">Jendela Check-in Belum Aktif atau Telah Berakhir</strong>
                <span>Check-in dapat dilakukan mulai <strong>{{ $windowStart->format('H:i') }} WIB</strong> (30 menit sebelum sesi) hingga <strong>{{ $windowEnd->format('H:i') }} WIB</strong>.</span>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <strong class="font-bold">Jendela Check-in Sedang Aktif</strong>
                <span class="text-xs text-emerald-700">(Batas waktu check-in: pukul {{ $windowEnd->format('H:i') }} WIB)</span>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">Siap Scan</span>
        </div>
    @endif

    {{-- Flash message server jika ada --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-900">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Layout Utama: 2 Kolom (Kamera Scanner & Daftar Kehadiran) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Kolom Kiri: Pemindai Kamera & Kartu Hasil Scan (lg:col-span-5) --}}
        <div class="lg:col-span-5 space-y-5">
            {{-- Card Scanner Kamera --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Pemindai QR Code</h2>
                        <p class="text-xs text-slate-500">Kamera belakang ponsel atau webcam</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                @click="toggleCamera()"
                                :disabled="scannerLoading"
                                :class="scannerRunning ? 'bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100' : (scannerLoading ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100')"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1">
                            <span x-text="scannerRunning ? '⏹ Matikan Kamera' : (scannerLoading ? '⏳ Memuat...' : '▶ Nyalakan Kamera')"></span>
                        </button>
                    </div>
                </div>

                {{-- Area Kamera Viewport --}}
                <div class="p-4 bg-slate-950 flex flex-col items-center justify-center min-h-[300px] relative">
                    <div id="reader" class="w-full max-w-[360px] mx-auto rounded-xl overflow-hidden"></div>

                    {{-- Loading spinner saat kamera sedang diinisialisasi --}}
                    <div x-show="scannerLoading" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-3">
                        <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-600 border-t-blue-400"></div>
                        <p class="text-xs text-slate-400 font-semibold">Meminta izin kamera...</p>
                        <p class="text-[11px] text-slate-500 text-center max-w-[240px]">
                            Jika dialog izin muncul, klik <strong class="text-slate-300">Izinkan / Allow</strong>
                        </p>
                    </div>

                    {{-- Placeholder Saat Kamera Belum Menyala --}}
                    <div x-show="!scannerRunning && !scannerLoading && !cameraError" class="text-center py-10 px-4 space-y-3">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-800 text-slate-300 flex items-center justify-center text-2xl">
                            📷
                        </div>
                        <p class="text-xs text-slate-300 max-w-xs mx-auto">
                            Klik tombol di bawah untuk menyalakan kamera dan memindai QR Code peserta secara otomatis.
                        </p>
                        <p class="text-[11px] text-slate-500 max-w-xs mx-auto leading-relaxed">
                            💡 Saat dialog izin kamera muncul di browser, pilih <strong class="text-slate-400">Izinkan / Allow</strong>
                        </p>
                        <button type="button"
                                @click="startScanner()"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md transition">
                            Aktifkan Kamera Sekarang
                        </button>
                    </div>

                    {{-- Pesan Error Izin Kamera / Tidak Ada Kamera --}}
                    <div x-show="cameraError" x-cloak class="text-center py-8 px-4 space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full bg-rose-900/60 text-rose-300 flex items-center justify-center text-xl">
                            ⚠️
                        </div>
                        <strong class="text-xs font-bold text-rose-300 block">Kamera Tidak Dapat Diakses</strong>
                        <p class="text-[11px] text-slate-400 max-w-xs mx-auto" x-text="cameraErrorMessage"></p>
                        <p class="text-[11px] text-slate-400">
                            Gunakan tombol <strong>Check-in Manual</strong> pada tabel di samping untuk mencatat kehadiran peserta secara langsung.
                        </p>
                        <button type="button"
                                @click="startScanner()"
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                            Coba Akses Lagi
                        </button>
                    </div>
                </div>

                {{-- Footer Scanner: Tips & Panduan --}}
                <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Arahkan kamera ke layar ponsel peserta. QR Code di-refresh berkala untuk mencegah kecurangan.
                    </p>
                </div>
            </div>

            {{-- Card Hasil Pemindaian Terkini --}}
            <div class="rounded-2xl border p-5 shadow-sm transition-all"
                 :class="{
                     'border-slate-200 bg-white': scanResult.status === 'idle',
                     'border-blue-300 bg-blue-50/70': scanResult.status === 'processing',
                     'border-emerald-300 bg-emerald-50/80': scanResult.status === 'success',
                     'border-rose-300 bg-rose-50/80': scanResult.status === 'error'
                 }">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500">Hasil Pemindaian Terkini</span>
                    <span x-show="scanCooldown" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                        Jeda Scan (2.5s)
                    </span>
                </div>

                {{-- Status Idle --}}
                <template x-if="scanResult.status === 'idle'">
                    <div class="py-6 text-center text-slate-400 space-y-1">
                        <span class="text-2xl block">🔍</span>
                        <p class="text-xs font-semibold">Siap memindai QR Code peserta.</p>
                        <p class="text-[11px]">Hasil verifikasi akan langsung muncul di sini.</p>
                    </div>
                </template>

                {{-- Status Processing --}}
                <template x-if="scanResult.status === 'processing'">
                    <div class="py-6 text-center text-blue-700 space-y-2">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                        <p class="text-xs font-bold">Memverifikasi token QR ke server...</p>
                    </div>
                </template>

                {{-- Status Success --}}
                <template x-if="scanResult.status === 'success'">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <template x-if="scanResult.user.avatar">
                                <img :src="scanResult.user.avatar" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-400 shadow-sm shrink-0" alt="Avatar">
                            </template>
                            <template x-if="!scanResult.user.avatar">
                                <div class="w-16 h-16 rounded-full bg-emerald-200 text-emerald-800 font-black text-2xl flex items-center justify-center shrink-0 border-2 border-emerald-400 shadow-sm">
                                    <span x-text="scanResult.user.name ? scanResult.user.name.charAt(0).toUpperCase() : 'U'"></span>
                                </div>
                            </template>
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full mb-1">
                                    ✓ Check-in Berhasil
                                </span>
                                <h3 class="text-lg font-black text-slate-900 truncate" x-text="scanResult.user.name"></h3>
                                <p class="text-xs text-slate-500 font-semibold">
                                    Jam Hadir: <span class="text-slate-800 font-bold" x-text="scanResult.check_in_at"></span> WIB
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-emerald-200/60 flex flex-wrap items-center gap-2">
                            <template x-if="scanResult.points_awarded">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-xs">
                                    +<span x-text="scanResult.points_amount"></span> Poin Diberikan
                                </span>
                            </template>
                            <template x-if="!scanResult.points_awarded">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">
                                    Hadir (Tanpa Poin/Streak)
                                </span>
                            </template>
                            <template x-if="scanResult.current_streak > 0">
                                <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200">
                                    ⚡ Streak: <span x-text="scanResult.current_streak"></span> Pekan
                                </span>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Status Error --}}
                <template x-if="scanResult.status === 'error'">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl shrink-0">🚫</span>
                            <div>
                                <strong class="text-sm font-bold text-rose-800 block">Check-in Ditolak</strong>
                                <p class="mt-1 text-xs text-rose-700 leading-relaxed" x-text="scanResult.message"></p>
                                <span class="mt-2 inline-block text-[10px] uppercase font-mono px-2 py-0.5 rounded bg-rose-200/70 text-rose-800 font-bold" x-text="scanResult.error_code"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan Kehadiran & Tabel Peserta (lg:col-span-7) --}}
        <div class="lg:col-span-7 space-y-5">
            {{-- Metric Ringkasan Kehadiran --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Hadir</span>
                    <strong class="text-2xl font-black text-emerald-600 block mt-1" x-text="counts.attended"></strong>
                    <span class="text-[11px] text-slate-500 font-medium">Peserta terverifikasi</span>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Belum Hadir</span>
                    <strong class="text-2xl font-black text-blue-600 block mt-1" x-text="counts.registered"></strong>
                    <span class="text-[11px] text-slate-500 font-medium">Menunggu check-in</span>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Terdaftar</span>
                    <strong class="text-2xl font-black text-slate-800 block mt-1" x-text="counts.total"></strong>
                    <span class="text-[11px] text-slate-500 font-medium">
                        / {{ $schedule->capacity ? $schedule->capacity . ' Kuota' : '∞ Kuota' }}
                    </span>
                </div>
            </div>

            {{-- Progress Bar Kehadiran --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-2">
                    <span>Persentase Kehadiran Sesi</span>
                    <span x-text="attendancePercentage() + '% (' + counts.attended + ' dari ' + counts.total + ' hadir)'"></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-emerald-500 h-2.5 rounded-full transition-all duration-500"
                         :style="'width: ' + attendancePercentage() + '%'"></div>
                </div>
            </div>

            {{-- Card Tabel Peserta --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                {{-- Header Tabel & Filter --}}
                <div class="p-4 border-b border-slate-100 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Daftar Peserta Sesi</h2>
                            <p class="text-xs text-slate-500">Auto-update berkala setiap 15 detik</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    @click="fetchParticipants()"
                                    :disabled="refreshing"
                                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition flex items-center gap-1">
                                <span :class="{'animate-spin': refreshing}">🔄</span>
                                <span x-text="refreshing ? 'Memperbarui...' : 'Segarkan'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Search & Tab Filter --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-1">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari nama peserta..."
                                   class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div class="flex items-center gap-1 p-0.5 rounded-xl bg-slate-100 self-start">
                            <button type="button"
                                    @click="statusFilter = 'all'"
                                    :class="statusFilter === 'all' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                Semua (<span x-text="counts.total"></span>)
                            </button>
                            <button type="button"
                                    @click="statusFilter = 'registered'"
                                    :class="statusFilter === 'registered' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                Belum (<span x-text="counts.registered"></span>)
                            </button>
                            <button type="button"
                                    @click="statusFilter = 'attended'"
                                    :class="statusFilter === 'attended' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                Hadir (<span x-text="counts.attended"></span>)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Konten List / Tabel Peserta --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">Peserta</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Jam Hadir</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="p in filteredParticipants()" :key="p.id">
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <template x-if="p.user_avatar">
                                                <img :src="p.user_avatar" class="w-8 h-8 rounded-full object-cover shrink-0" alt="Avatar">
                                            </template>
                                            <template x-if="!p.user_avatar">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                                    <span x-text="p.user_name ? p.user_name.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                            </template>
                                            <div class="min-w-0 max-w-[180px] sm:max-w-[220px]">
                                                <strong class="block text-slate-900 truncate font-bold" x-text="p.user_name"></strong>
                                                <span class="block text-[11px] text-slate-400 truncate" x-text="p.user_email"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <template x-if="p.status === 'attended'">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span>✓</span> Hadir
                                            </span>
                                        </template>
                                        <template x-if="p.status === 'registered'">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span>⏱</span> Belum Hadir
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap text-slate-500 font-medium">
                                        <template x-if="p.check_in_time">
                                            <span>
                                                <strong class="text-slate-800" x-text="p.check_in_time"></strong> WIB
                                                <span class="text-[10px] text-slate-400 block" x-text="p.method === 'qr_scan' ? 'via QR' : 'Manual'"></span>
                                            </span>
                                        </template>
                                        <template x-if="!p.check_in_time">
                                            <span class="text-slate-400">—</span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <template x-if="p.status === 'registered'">
                                            <button type="button"
                                                    @click="doManualCheckin(p)"
                                                    :disabled="p.isProcessing"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-[11px] shadow-xs transition">
                                                <span x-show="p.isProcessing" class="animate-spin text-[10px]">🔄</span>
                                                <span x-text="p.isProcessing ? 'Memproses...' : 'Check-in Manual'"></span>
                                            </button>
                                        </template>
                                        <template x-if="p.status === 'attended'">
                                            <span class="text-xs font-semibold text-emerald-600">✓ Selesai</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="filteredParticipants().length === 0">
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <span class="text-xl block mb-1">👥</span>
                                        <p class="text-xs">Tidak ada data peserta yang cocok.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkinScanner(config) {
    return {
        scheduleId: config.scheduleId,
        scanEndpoint: config.scanEndpoint,
        participantsEndpoint: config.participantsEndpoint,
        manualCheckinBase: config.manualCheckinBase,
        csrfToken: config.csrfToken,
        participants: config.initialParticipants || [],
        counts: config.initialCounts || { total: 0, attended: 0, registered: 0 },
        isToday: config.isToday,
        isWindowOpen: config.isWindowOpen,

        html5QrCode: null,
        scannerRunning: false,
        scannerLoading: false,
        cameraError: false,
        cameraErrorMessage: '',
        lastScannedToken: '',
        scanCooldown: false,
        refreshing: false,
        searchQuery: '',
        statusFilter: 'all',
        pollIntervalTimer: null,

        scanResult: {
            status: 'idle', // 'idle' | 'processing' | 'success' | 'error'
            message: '',
            error_code: '',
            user: { id: null, name: '', avatar: null },
            check_in_at: '',
            points_awarded: false,
            points_amount: 0,
            current_streak: 0,
            total_points: 0
        },

        initScanner() {
            // Polling berkala setiap 15 detik
            this.pollIntervalTimer = setInterval(() => {
                this.fetchParticipants(true);
            }, 15000);
            // Tidak auto-start kamera — user klik tombol sendiri agar browser tidak
            // membatalkan dialog izin kamera karena dipicu sebelum ada interaksi user.
        },

        async startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                this.cameraError = true;
                this.cameraErrorMessage = 'Library pemindai QR belum termuat. Silakan periksa koneksi internet dan muat ulang halaman.';
                return;
            }

            if (this.scannerRunning || this.scannerLoading) return;

            // Cek secure context lebih awal dan beri pesan jelas
            if (!window.isSecureContext) {
                this.cameraError = true;
                this.cameraErrorMessage = 'Kamera memerlukan koneksi aman (HTTPS). Hubungi admin untuk mengakses via HTTPS.';
                return;
            }

            this.cameraError = false;
            this.cameraErrorMessage = '';
            this.scannerLoading = true;

            const qrConfig = {
                fps: 10,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0,
                verbose: false,
                // Eksplisit minta kamera agar browser tidak dismiss dialog izin
                videoConstraints: {
                    facingMode: { ideal: 'environment' }
                }
            };

            const onScan = (decodedText) => this.onScanSuccess(decodedText);
            const onErr  = () => { /* abaikan error per-frame */ };

            try {
                if (this.html5QrCode) {
                    try { await this.html5QrCode.stop(); } catch(e) {}
                    try { this.html5QrCode.clear(); } catch(e) {}
                    this.html5QrCode = null;
                }

                this.html5QrCode = new Html5Qrcode('reader', { verbose: false });

                // Langsung minta getUserMedia dulu — ini memastikan browser sudah
                // menampilkan dialog izin kamera sebelum getCameras() dipanggil.
                // Tanpa ini, Chrome 125+ kadang dismiss dialog jika belum ada izin aktif.
                let preStream = null;
                try {
                    preStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                } catch (permErr) {
                    this.handleCameraError(permErr);
                    return;
                }
                // Hentikan stream sementara — Html5Qrcode akan membuka stream sendiri
                if (preStream) {
                    preStream.getTracks().forEach(t => t.stop());
                }

                // Ambil daftar kamera setelah izin diperoleh
                let cameras = [];
                try {
                    cameras = await Html5Qrcode.getCameras();
                } catch (e) {
                    cameras = [];
                }

                if (cameras && cameras.length > 0) {
                    // Prioritaskan kamera belakang (environment) untuk smartphone
                    let selectedId = cameras[0].id;
                    for (const cam of cameras) {
                        const label = (cam.label || '').toLowerCase();
                        if (label.includes('back') || label.includes('rear') ||
                            label.includes('belakang') || label.includes('environment')) {
                            selectedId = cam.id;
                            break;
                        }
                    }
                    await this.html5QrCode.start(selectedId, qrConfig, onScan, onErr);
                } else {
                    // Fallback: pakai facingMode object (lebih stabil dari string di Chrome)
                    await this.html5QrCode.start(
                        { facingMode: { ideal: 'environment' } },
                        qrConfig, onScan, onErr
                    );
                }

                this.scannerLoading = false;
                this.scannerRunning = true;

            } catch (err) {
                this.handleCameraError(err);
            }
        },

        handleCameraError(err) {
            this.scannerLoading = false;
            this.scannerRunning = false;
            if (this.html5QrCode) {
                try { this.html5QrCode.stop(); } catch(e) {}
                try { this.html5QrCode.clear(); } catch(e) {}
                this.html5QrCode = null;
            }
            this.cameraError = true;

            const name = (err?.name || '').toLowerCase();
            const msg  = (err?.message || err || '').toString().toLowerCase();

            if (name === 'notallowederror' || msg.includes('permission') || msg.includes('denied') || msg.includes('notallowed')) {
                this.cameraErrorMessage = 'Izin kamera ditolak. Klik ikon 🔒 di address bar browser → "Izinkan" kamera, lalu klik tombol "Coba Akses Lagi" di bawah.';
            } else if (name === 'notfounderror' || msg.includes('notfound') || msg.includes('devicesnotfound') || msg.includes('no camera')) {
                this.cameraErrorMessage = 'Tidak ada kamera yang terdeteksi. Pastikan kamera sudah terhubung dan tidak diblokir sistem.';
            } else if (name === 'notreadableerror' || msg.includes('in use') || msg.includes('busy') || msg.includes('notreadable') || msg.includes('could not start')) {
                this.cameraErrorMessage = 'Kamera sedang digunakan aplikasi lain (Zoom, Teams, tab browser lain, dsb). Tutup aplikasi tersebut lalu coba lagi.';
            } else if (name === 'overconstrainederror') {
                this.cameraErrorMessage = 'Kamera tidak mendukung konfigurasi yang diminta. Silakan coba lagi atau gunakan Check-in Manual.';
            } else {
                this.cameraErrorMessage = err?.message || 'Kamera tidak dapat diakses. Gunakan tombol Check-in Manual di tabel sebelah kanan.';
            }
        },

        stopScanner() {
            if (this.html5QrCode && this.scannerRunning) {
                this.html5QrCode.stop().then(() => {
                    this.scannerRunning = false;
                    try { this.html5QrCode.clear(); } catch(e) {}
                    this.html5QrCode = null;
                }).catch(err => {
                    console.error('Gagal menghentikan kamera:', err);
                    this.scannerRunning = false;
                    this.html5QrCode = null;
                });
            }
        },

        toggleCamera() {
            if (this.scannerRunning) {
                this.stopScanner();
            } else {
                this.startScanner();
            }
        },

        onScanSuccess(token) {
            // Hindari scan ganda berulang dalam jeda cooldown 2.5 detik
            if (this.scanCooldown || !token) return;
            if (this.lastScannedToken === token && this.scanResult.status === 'success') return;

            this.scanCooldown = true;
            this.lastScannedToken = token;
            this.scanResult.status = 'processing';

            fetch(this.scanEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ token: token })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(({ status, ok, data }) => {
                if (ok && data.success) {
                    this.scanResult = {
                        status: 'success',
                        message: data.message,
                        error_code: '',
                        user: data.user || {},
                        check_in_at: data.check_in_at,
                        points_awarded: data.points_awarded,
                        points_amount: data.points_amount,
                        current_streak: data.current_streak,
                        total_points: data.total_points
                    };
                    // Segarkan daftar peserta langsung setelah scan berhasil
                    this.fetchParticipants(true);
                } else {
                    this.scanResult = {
                        status: 'error',
                        message: data.message || 'Token QR tidak valid.',
                        error_code: data.error_code || 'token_invalid',
                        user: {},
                        check_in_at: '',
                        points_awarded: false,
                        points_amount: 0,
                        current_streak: 0,
                        total_points: 0
                    };
                }
            })
            .catch(err => {
                this.scanResult = {
                    status: 'error',
                    message: 'Gagal menghubungi server saat verifikasi QR.',
                    error_code: 'network_error',
                    user: {},
                    check_in_at: '',
                    points_awarded: false,
                    points_amount: 0,
                    current_streak: 0,
                    total_points: 0
                };
            })
            .finally(() => {
                // Jeda 2.5 detik sebelum scan berikutnya diizinkan
                setTimeout(() => {
                    this.scanCooldown = false;
                }, 2500);
            });
        },

        doManualCheckin(participant) {
            if (!confirm(`Konfirmasi check-in manual untuk ${participant.user_name}?`)) return;

            participant.isProcessing = true;
            const endpoint = `${this.manualCheckinBase}/${participant.id}`;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    this.scanResult = {
                        status: 'success',
                        message: data.message,
                        error_code: '',
                        user: data.user || { name: participant.user_name },
                        check_in_at: data.check_in_at,
                        points_awarded: data.points_awarded,
                        points_amount: data.points_amount,
                        current_streak: data.current_streak,
                        total_points: data.total_points
                    };
                    this.fetchParticipants(true);
                } else {
                    alert(data.message || 'Gagal memproses check-in manual.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan saat check-in manual.');
            })
            .finally(() => {
                participant.isProcessing = false;
            });
        },

        fetchParticipants(isSilent = false) {
            if (!isSilent) this.refreshing = true;

            fetch(this.participantsEndpoint, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.participants) {
                    this.participants = data.participants;
                }
                if (data.summary) {
                    this.counts = data.summary;
                }
            })
            .catch(err => {
                console.error('Error auto-refreshing participants:', err);
            })
            .finally(() => {
                if (!isSilent) this.refreshing = false;
            });
        },

        filteredParticipants() {
            return this.participants.filter(p => {
                // Filter tab status
                if (this.statusFilter !== 'all' && p.status !== this.statusFilter) {
                    return false;
                }
                // Filter search input
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    const nameMatch = (p.user_name || '').toLowerCase().includes(q);
                    const emailMatch = (p.user_email || '').toLowerCase().includes(q);
                    return nameMatch || emailMatch;
                }
                return true;
            });
        },

        attendancePercentage() {
            if (!this.counts.total || this.counts.total === 0) return 0;
            return Math.min(100, Math.round((this.counts.attended / this.counts.total) * 100));
        }
    };
}
</script>
@endpush

