@extends('layouts.admin')

@section('title', 'Detail Komunitas: ' . $community->name . ' — Console Ops')

@section('content')
<div x-data="{ openRevokeModal: {{ $errors->has('reason') ? 'true' : 'false' }} }"
     class="min-h-screen bg-[#0B1120] text-slate-100 p-6 lg:p-8 space-y-6 font-sans">

    {{-- Breadcrumb & Tombol Kembali --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.communities.index') }}" class="hover:text-cyan-400 transition">Klub & Komunitas</a>
            <span>/</span>
            <span class="text-slate-200 font-medium">{{ $community->name }}</span>
        </div>

        <a href="{{ route('admin.communities.index') }}"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 transition">
            &larr; Kembali ke Daftar
        </a>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm">
            <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
            <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Profil Komunitas & Status --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#111827] via-[#131C31] to-[#0F172A] border border-slate-800 p-6 lg:p-7 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-600/20 border border-blue-500/30 text-blue-400 text-xl font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($community->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-2">
                        {{-- Badge Status Verifikasi --}}
                        @if($community->verification_status === 'verified')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Terverifikasi (Verified)
                            </span>
                        @elseif($community->verification_status === 'suspended')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Suspended (Verifikasi Dicabut)
                            </span>
                        @elseif($community->verification_status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Menunggu Verifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                {{ ucfirst($community->verification_status) }}
                            </span>
                        @endif

                        {{-- Badge Status Operasional --}}
                        @if($community->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                                Status: Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                Status: Nonaktif
                            </span>
                        @endif

                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                            {{ ucfirst($community->sport_category) }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ $community->name }}</h1>
                    <p class="text-sm text-slate-400 mt-1 max-w-2xl">
                        {{ $community->description ?? 'Tidak ada deskripsi komunitas.' }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-400">
                        {{-- Ikon SVG Lokasi --}}
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ collect([$community->location, $community->city, $community->province])->filter()->join(', ') ?: 'Lokasi belum diatur' }}
                        </span>
                        {{-- Ikon SVG Kalender --}}
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Dibuat {{ $community->created_at?->translatedFormat('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Panel Tombol Aksi Admin --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                {{-- Aksi 1: Aktifkan / Nonaktifkan --}}
                <form method="POST" action="{{ route('admin.communities.toggle-active', $community) }}"
                      onsubmit="return confirm('{{ $community->is_active ? 'Nonaktifkan komunitas ini? Komunitas tidak akan tampil aktif di publik.' : 'Aktifkan kembali komunitas ini?' }}')">
                    @csrf
                    @method('PATCH')
                    @if($community->is_active)
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 transition cursor-pointer">
                            Nonaktifkan Komunitas
                        </button>
                    @else
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition cursor-pointer">
                            Aktifkan Komunitas
                        </button>
                    @endif
                </form>

                {{-- Aksi 2: Cabut Verifikasi (Hanya muncul jika saat ini 'verified') --}}
                @if($community->verification_status === 'verified')
                    <button type="button" @click="openRevokeModal = true"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition cursor-pointer">
                        Cabut Verifikasi (Suspend)
                    </button>
                @endif
            </div>
        </div>

        {{-- Banner Alasan Jika Suspended / Rejected --}}
        @if(in_array($community->verification_status, ['suspended', 'rejected']) && $community->rejection_reason)
            <div class="mt-5 p-4 rounded-xl bg-rose-950/40 border border-rose-500/30">
                <div class="text-xs font-bold uppercase tracking-wider text-rose-400">
                    {{ $community->verification_status === 'suspended' ? 'Alasan Pencabutan Verifikasi (Suspended)' : 'Alasan Penolakan' }}
                </div>
                <p class="text-sm text-rose-200 mt-1">{{ $community->rejection_reason }}</p>
                @if($community->verification_status === 'suspended')
                    <p class="text-xs text-rose-300/80 mt-2">
                        * Dampak sistem: Pemberian poin event dan penambahan streak baru otomatis dihentikan saat check-in karena status verifikasi tidak lagi <code>verified</code>.
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- 4 Kartu Metrik Utama (Pengikut, Anggota, Jadwal, Event) dengan Ikon SVG --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Pengikut</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-bold text-white mt-3">{{ number_format($community->followers_count) }}</div>
            <div class="text-xs text-slate-500 mt-1">Akun yang mengikuti komunitas</div>
        </div>

        <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Anggota</span>
                <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-bold text-white mt-3">{{ number_format($community->members_count) }}</div>
            <div class="text-xs text-slate-500 mt-1">
                Kapasitas: {{ $community->member_limit ? number_format($community->member_limit) . ' anggota' : 'Tidak dibatasi' }}
            </div>
        </div>

        <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Jadwal</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-bold text-white mt-3">{{ number_format($community->schedules_count) }}</div>
            <div class="text-xs text-slate-500 mt-1">Total sesi latihan terdaftar</div>
        </div>

        <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Event</span>
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-bold text-white mt-3">{{ number_format($community->events_count) }}</div>
            <div class="text-xs text-slate-500 mt-1">Turnamen & event komunitas</div>
        </div>
    </div>

    {{-- Detail Pemilik (Owner) & Informasi Kontak --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kartu Pemilik Komunitas --}}
        <div class="rounded-2xl bg-[#111827] border border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-3">
                Pemilik Komunitas (Owner)
            </h2>

            @if($community->owner)
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-full bg-slate-800 border border-slate-700 text-cyan-400 font-bold flex items-center justify-center">
                        {{ strtoupper(substr($community->owner->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-bold text-white">{{ $community->owner->name }}</div>
                        <div class="text-xs text-slate-400">{{ $community->owner->email }}</div>
                    </div>
                </div>

                <dl class="space-y-2.5 pt-2 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <dt class="text-slate-400">Username</dt>
                        <dd class="text-slate-200 font-medium">{{ $community->owner->username ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <dt class="text-slate-400">No. Telepon</dt>
                        <dd class="text-slate-200 font-medium">{{ $community->owner->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <dt class="text-slate-400">Status Akun Owner</dt>
                        <dd>
                            @if($community->owner->is_active)
                                <span class="text-emerald-400 font-semibold">Aktif</span>
                            @else
                                <span class="text-rose-400 font-semibold">Nonaktif</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <dt class="text-slate-400">Diverifikasi Pada</dt>
                        <dd class="text-slate-200 font-medium">
                            {{ $community->verified_at ? $community->verified_at->translatedFormat('d M Y, H:i') : 'Belum / Dicabut' }}
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-xs text-slate-500">Data pemilik tidak ditemukan.</p>
            @endif
        </div>

        {{-- Aktivitas Jadwal & Event Terbaru --}}
        <div class="lg:col-span-2 rounded-2xl bg-[#111827] border border-slate-800 p-6 space-y-5">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-3">
                Ringkasan Jadwal & Event Terbaru
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Jadwal Terbaru --}}
                <div>
                    <h3 class="text-xs font-semibold text-cyan-400 uppercase tracking-wider mb-3">5 Jadwal Terakhir</h3>
                    <div class="space-y-2.5">
                        @forelse($community->schedules as $schedule)
                            <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800/80 flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs font-semibold text-white">{{ $schedule->title }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }} • {{ substr($schedule->start_time, 0, 5) }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300">
                                    {{ strtoupper($schedule->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 italic">Belum ada jadwal latihan.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Event Terbaru --}}
                <div>
                    <h3 class="text-xs font-semibold text-amber-400 uppercase tracking-wider mb-3">5 Event Terakhir</h3>
                    <div class="space-y-2.5">
                        @forelse($community->events as $event)
                            <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800/80 flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs font-semibold text-white">{{ $event->title }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') : 'Tanggal TBA' }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300">
                                    {{ strtoupper($event->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 italic">Belum ada event diselenggarakan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Cabut Verifikasi (Status Suspended, Alasan Wajib) --}}
    <div x-show="openRevokeModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-xs p-4">
        <div @click.away="openRevokeModal = false"
             class="w-full max-w-lg rounded-2xl bg-[#111827] border border-slate-700 p-6 shadow-2xl space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 mb-2">
                        Tindakan Tata Kelola Kritis
                    </span>
                    <h3 class="text-lg font-bold text-white">Cabut Verifikasi Komunitas</h3>
                </div>
                <button type="button" @click="openRevokeModal = false" class="text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-950/40 border border-amber-500/30 text-xs text-amber-200 space-y-1.5 leading-relaxed">
                <p class="font-semibold text-amber-300">Konsekuensi Otomatis Sistem:</p>
                <ul class="list-disc list-inside space-y-1 text-amber-200/90">
                    <li>Status verifikasi komunitas akan berubah menjadi <strong>Suspended</strong>.</li>
                    <li>Pemberian <strong>poin event</strong> dan perhitungan <strong>streak baru</strong> otomatis dihentikan saat peserta melakukan check-in (karena sistem memvalidasi status <code>verified</code> saat check-in).</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.communities.revoke-verification', $community) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="reason" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Alasan Pencabutan Verifikasi <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="reason" name="reason" rows="4" required
                        placeholder="Jelaskan pelanggaran atau alasan pencabutan verifikasi secara spesifik..."
                        class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-slate-100 placeholder-slate-500 p-3.5 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openRevokeModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/25 transition cursor-pointer">
                        Konfirmasi & Cabut Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection