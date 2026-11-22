@extends('layouts.admin')

@section('title', 'Detail laporan #' . $report->id . ' — Console admin Bugar.in')

@section('content')
<div class="space-y-6" x-data="{ showDismissModal: false, showWarnModal: false, showSancModal: false, showResolveModal: false }">

    {{-- Header & navigasi --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.reports.index') }}" class="hover:text-white transition-colors">Laporan</a>
                <span>/</span>
                <span class="text-rose-400 font-mono font-bold">#{{ $report->id }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                Detail laporan pelanggaran
            </h1>
        </div>

        <div>
            <a href="{{ route('admin.reports.index') }}"
               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-800 transition-all inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke daftar</span>
            </a>
        </div>
    </div>

    {{-- Flash messages ditangani terpusat oleh layout admin --}}
    @if($errors->any())
        <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs space-y-1">
            <div class="font-bold">Terjadi kesalahan validasi:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. IDENTITAS PELAPOR & TERLAPOR --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Card pelapor --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Identitas pelapor</h4>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 font-mono">Privat admin</span>
            </div>

            @if($report->reporter)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-extrabold flex items-center justify-center text-xs shadow-sm flex-shrink-0">
                        {{ strtoupper(substr($report->reporter->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5 text-xs">
                        <div class="font-bold text-white truncate">{{ $report->reporter->name }}</div>
                        <div class="text-slate-400 font-mono text-[11px] truncate">{{ $report->reporter->email }}</div>
                        <div class="text-[11px] text-slate-400">
                            Username: <span class="text-slate-300 font-mono">{{ '@' . ($report->reporter->username ?? '-') }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-xs text-slate-400 italic">Akun pelapor telah dihapus dari sistem.</div>
            @endif
        </div>

        {{-- Card terlapor --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Pihak terlapor</h4>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-mono font-bold uppercase">
                    {{ class_basename($report->reportable_type) }}
                </span>
            </div>

            @if($report->reportable)
                @php
                    $target = $report->reportable;
                    $targetType = class_basename($report->reportable_type);
                @endphp

                @if($targetType === 'Community')
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5 text-xs">
                            <div class="font-bold text-white truncate">{{ $target->name }}</div>
                            <div class="text-slate-400 text-[11px]">
                                {{ ucfirst($target->sport_category ?? 'Umum') }} &bull; {{ $target->city ?? 'Indonesia' }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Status:
                                <span class="font-semibold {{ $target->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $target->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @elseif($targetType === 'User')
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-600 to-orange-500 text-white font-extrabold flex items-center justify-center text-xs shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($target->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1 space-y-0.5 text-xs">
                            <div class="font-bold text-white truncate">{{ $target->name }}</div>
                            <div class="text-slate-400 font-mono text-[11px] truncate">{{ $target->email }}</div>
                            <div class="text-[11px] text-slate-400">
                                Status akun:
                                <span class="font-semibold {{ $target->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $target->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-xs text-slate-300">
                        ID: #{{ $report->reportable_id }} ({{ $report->reportable_type }})
                    </div>
                @endif
            @else
                <div class="text-xs text-slate-400 italic">Data entitas terlapor sudah tidak ditemukan di sistem.</div>
            @endif
        </div>

    </div>

    {{-- 2. INFORMASI LAPORAN & BUKTI --}}
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm sm:text-base font-bold text-white">{{ $report->reason }}</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $report->status_badge_class }}">
                            {{ $report->status_label }}
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">
                        Diajukan {{ $report->created_at->diffForHumans() }} ({{ $report->created_at->format('d/m/Y H:i') }})
                    </span>
                </div>
            </div>

            @if(in_array($report->status, ['resolved', 'dismissed']))
                <span class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 text-[10px] font-medium inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>Status final</span>
                </span>
            @endif
        </div>

        {{-- Deskripsi kronologi --}}
        <div class="space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Deskripsi kronologi</span>
            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-200 leading-relaxed whitespace-pre-line">
                {{ $report->description ?: 'Tidak ada deskripsi rinci yang disertakan.' }}
            </div>
        </div>

        {{-- Bukti pendukung --}}
        <div class="space-y-1.5 pt-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bukti pendukung</span>
            @if($report->evidence)
                <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs text-slate-300 truncate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                        <span class="font-mono text-[11px] truncate">{{ $report->evidence }}</span>
                    </div>
                    <a href="{{ $report->evidence }}" target="_blank" rel="noopener noreferrer"
                       class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition-colors flex-shrink-0 inline-flex items-center gap-1">
                        <span>Buka tautan</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            @endif

            @php
                $evidenceImages = $report->evidence_images;
                if (is_string($evidenceImages)) {
                    $decoded = json_decode($evidenceImages, true);
                    $evidenceImages = is_array($decoded) ? $decoded : (filled($evidenceImages) ? [$evidenceImages] : []);
                }
            @endphp

            @if(!empty($evidenceImages) && is_iterable($evidenceImages))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                    @foreach($evidenceImages as $imgPath)
                        @php
                            $fullUrl = \Illuminate\Support\Str::startsWith($imgPath, ['http://', 'https://'])
                                ? $imgPath
                                : asset('media/' . ltrim($imgPath, '/'));
                        @endphp
                        <a href="{{ $fullUrl }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-800 bg-slate-950 group relative">
                            <img src="{{ $fullUrl }}" alt="Bukti laporan" class="w-full h-24 object-cover group-hover:scale-105 transition-transform duration-200">
                            <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-semibold">
                                Perbesar &nearr;
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(!$report->evidence && empty($evidenceImages))
                <div class="p-3 rounded-xl bg-slate-950/40 border border-dashed border-slate-800 text-xs text-slate-500 italic">
                    Tidak ada bukti lampiran tambahan.
                </div>
            @endif
        </div>
    </div>

    {{-- 3. RIWAYAT KEPUTUSAN & TINDAKAN MODERASI --}}
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="pb-2.5 border-b border-slate-800 flex items-center justify-between">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Riwayat keputusan</h4>
            @if($report->handled_by)
                <span class="text-[10px] font-mono text-slate-400">
                    Ditangani {{ $report->handled_at ? $report->handled_at->diffForHumans() : '-' }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-400 block uppercase">Admin penanggung jawab</span>
                <div class="font-bold text-white mt-0.5">
                    {{ $report->handler->name ?? ($report->handled_by ? 'Admin #' . $report->handled_by : 'Belum ada') }}
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-400 block uppercase">Waktu penanganan final</span>
                <div class="font-bold text-white mt-0.5">
                    {{ $report->handled_at ? $report->handled_at->format('d/m/Y H:i') . ' WIB' : 'Menunggu tindakan' }}
                </div>
            </div>
        </div>

        {{-- Alasan penolakan jika ditolak --}}
        @if($report->status === 'dismissed' && ($report->rejection_reason || $report->admin_notes))
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs space-y-1">
                <span class="font-bold text-rose-300 block">Alasan penolakan laporan (terlihat oleh pelapor):</span>
                <p class="text-slate-200 leading-relaxed whitespace-pre-line">{{ $report->rejection_reason ?: $report->admin_notes }}</p>
            </div>
        @endif

        {{-- Catatan pemberitahuan / admin_notes --}}
        @if($report->admin_notes && $report->status !== 'dismissed')
            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs space-y-1">
                <span class="font-bold text-slate-300 block">Pemberitahuan hasil verifikasi ke pelapor:</span>
                <p class="text-slate-300 leading-relaxed whitespace-pre-line">{{ $report->admin_notes }}</p>
            </div>
        @endif

        {{-- Log tindakan / peringatan (action_note) --}}
        @if($report->action_note)
            <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs space-y-1">
                <span class="font-bold text-amber-300 block">Catatan tindakan &amp; peringatan resmi:</span>
                <p class="text-slate-200 leading-relaxed whitespace-pre-line font-mono text-[11px]">{{ $report->action_note }}</p>
            </div>
        @endif
    </div>

    {{-- 4. KEPUTUSAN STATUS (SETUJUI / TOLAK) --}}
    @php
        $isFinal = in_array($report->status, ['resolved', 'dismissed']);
        $isCommunity = class_basename($report->reportable_type) === 'Community';
        $isUser = class_basename($report->reportable_type) === 'User';
        $targetObj = $report->reportable;
    @endphp

    @if(!$isFinal)
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="pb-2.5 border-b border-slate-800">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Keputusan moderasi</h4>
                <p class="text-xs text-slate-400 mt-0.5">Tentukan persetujuan atau penolakan laporan ini.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Tombol Setujui --}}
                <div class="flex-1">
                    <button type="button"
                            @click="showResolveModal = true"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all inline-flex items-center justify-center gap-2 shadow-sm shadow-emerald-600/20 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Setujui laporan (terbukti valid)</span>
                    </button>
                </div>

                {{-- Tombol Tolak --}}
                <div class="flex-1">
                    <button type="button"
                            @click="showDismissModal = true"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-rose-300 hover:text-rose-200 border border-slate-700 hover:border-rose-500/30 font-bold text-xs transition-all inline-flex items-center justify-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Tolak laporan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. AKSI PERINGATAN KE KOMUNITAS (JIKA TARGET KOMUNITAS) --}}
    @if($isCommunity && $targetObj)
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-800">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Peringatan komunitas</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Kirim teguran resmi dengan template pesan pelanggaran standar.</p>
                </div>
                <div>
                    @if($report->status === 'resolved')
                        <button type="button"
                                @click="showWarnModal = true"
                                class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all inline-flex items-center gap-2 cursor-pointer shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span>Beri peringatan ke komunitas</span>
                        </button>
                    @else
                        <button type="button" disabled
                                class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-500 font-semibold text-xs border border-slate-700/50 cursor-not-allowed inline-flex items-center gap-2"
                                title="Laporan harus disetujui terlebih dahulu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span>Beri peringatan ke komunitas</span>
                        </button>
                    @endif
                </div>
            </div>
            @if($report->status !== 'resolved')
                <p class="text-[11px] text-slate-500 italic">
                    Tombol peringatan aktif setelah laporan disetujui admin.
                </p>
            @endif
        </div>
    @endif

    {{-- 6. SANKSI NONAKTIFKAN (DITARUH DI PALING BAWAH, AKTIF JIKA STATUS RESOLVED) --}}
    @if($targetObj && ($isCommunity || $isUser))
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2.5 border-b border-slate-800">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-400">
                        Sanksi nonaktifkan {{ $isCommunity ? 'komunitas' : 'pengguna' }}
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Status saat ini:
                        <span class="font-bold {{ $targetObj->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $targetObj->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                        </span>
                    </p>
                </div>

                <div>
                    @if($report->status === 'resolved')
                        <button type="button"
                                @click="showSancModal = true"
                                class="px-4 py-2 rounded-xl {{ $targetObj->is_active ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-600/20' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20' }} font-bold text-xs shadow-sm transition-all inline-flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span>{{ $targetObj->is_active ? 'Nonaktifkan sementara' : 'Aktifkan kembali' }}</span>
                        </button>
                    @else
                        <button type="button" disabled
                                class="px-4 py-2 rounded-xl bg-slate-800 text-slate-500 font-semibold text-xs border border-slate-700/50 cursor-not-allowed inline-flex items-center gap-2"
                                title="Selesaikan atau setujui laporan terlebih dahulu sebelum sanksi dapat dijatuhkan">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <span>Nonaktifkan sementara</span>
                        </button>
                    @endif
                </div>
            </div>

            @if($report->status !== 'resolved')
                <p class="text-[11px] text-slate-500 italic">
                    Tombol sanksi nonaktifkan hanya dapat diklik setelah laporan disetujui oleh admin.
                </p>
            @endif
        </div>
    @endif

    {{-- MODAL 0: SETUJUI LAPORAN --}}
    <div x-show="showResolveModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
         style="display: none;"
         x-transition>
        <div @click.away="showResolveModal = false"
             class="w-full max-w-md p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Setujui laporan #{{ $report->id }}</h3>
                </div>
                <button type="button" @click="showResolveModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}" class="space-y-3">
                @csrf
                <p class="text-xs text-slate-300 leading-relaxed">
                    Laporan ini akan ditandai valid dan selesai. Anda dapat menambahkan catatan investigasi untuk pelapor (opsional).
                </p>

                <div>
                    <label for="resolve_admin_notes" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        Catatan admin (opsional)
                    </label>
                    <textarea id="resolve_admin_notes" name="admin_notes" rows="3" maxlength="1000"
                              placeholder="Biarkan kosong untuk menggunakan pesan standar sistem..."
                              class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-emerald-500 transition-colors resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showResolveModal = false"
                            class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all cursor-pointer">
                        Ya, setujui laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 1: TOLAK LAPORAN --}}
    <div x-show="showDismissModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
         style="display: none;"
         x-transition>
        <div @click.away="showDismissModal = false"
             class="w-full max-w-md p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tolak laporan #{{ $report->id }}</h3>
                </div>
                <button type="button" @click="showDismissModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="rejection_reason" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                        Alasan penolakan <span class="text-rose-400">* (10–1000 karakter)</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required minlength="10" maxlength="1000"
                              placeholder="Tulis alasan penolakan yang akan ditampilkan kepada pelapor..."
                              class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-rose-500 transition-colors resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showDismissModal = false"
                            class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all cursor-pointer">
                        Konfirmasi tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: PERINGATAN RESMI KOMUNITAS --}}
    @if($isCommunity && $targetObj)
        <div x-show="showWarnModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
             style="display: none;"
             x-transition>
            <div @click.away="showWarnModal = false"
                 class="w-full max-w-lg p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Kirim peringatan ke komunitas</h3>
                    </div>
                    <button type="button" @click="showWarnModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.reports.community.warn', $report->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="warning_message" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                            Pesan peringatan resmi
                        </label>
                        <p class="text-[11px] text-slate-400 mb-2">
                            Pesan default berikut dapat disesuaikan sebelum dikirimkan ke komunitas terlapor:
                        </p>
                        <textarea id="warning_message" name="warning_message" rows="4" maxlength="1000"
                                  class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-amber-500 transition-colors resize-none leading-relaxed">Peringatan resmi dari tim moderasi Bugar.in: Komunitas '{{ $targetObj->name }}' telah terbukti melanggar aturan platform pada laporan #{{ $report->id }} (alasan: {{ $report->reason }}). Harap segera mematuhi regulasi komunitas. Jika pelanggaran berulang, komunitas akan dinonaktifkan secara sepihak.</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="showWarnModal = false"
                                class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition-all cursor-pointer">
                            Kirim peringatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 3: KONFIRMASI SANKSI NONAKTIFKAN --}}
    @if($targetObj && ($isCommunity || $isUser))
        <div x-show="showSancModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
             style="display: none;"
             x-transition>
            <div @click.away="showSancModal = false"
                 class="w-full max-w-md p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                            Konfirmasi {{ $targetObj->is_active ? 'penonaktifan' : 'pengaktifan' }}
                        </h3>
                    </div>
                    <button type="button" @click="showSancModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
                </div>

                <form method="POST"
                      action="{{ $isCommunity ? route('admin.reports.community.toggle-status', $targetObj->id) : route('admin.reports.user.toggle-status', $targetObj->id) }}"
                      class="space-y-3">
                    @csrf
                    <input type="hidden" name="report_id" value="{{ $report->id }}">

                    <p class="text-xs text-slate-300 leading-relaxed">
                        Anda akan mengubah status <strong class="text-white">{{ $targetObj->name }}</strong> menjadi <strong class="{{ $targetObj->is_active ? 'text-rose-400' : 'text-emerald-400' }}">{{ $targetObj->is_active ? 'nonaktif' : 'aktif' }}</strong>.
                    </p>

                    @if($targetObj->is_active)
                        <div>
                            <label for="sanc_reason" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                                Alasan penonaktifan <span class="text-rose-400">* (min. 5 karakter)</span>
                            </label>
                            <textarea id="sanc_reason" name="reason" rows="3" required minlength="5" maxlength="1000"
                                      placeholder="Tuliskan alasan penonaktifan berdasarkan bukti laporan yang terbukti..."
                                      class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-rose-500 transition-colors resize-none"></textarea>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="showSancModal = false"
                                class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-lg {{ $targetObj->is_active ? 'bg-rose-600 hover:bg-rose-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white text-xs font-bold transition-all cursor-pointer">
                            Ya, proses sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
