@extends('layouts.admin')

@section('title', 'Detail Laporan #' . $report->id . ' — Console Admin Bugar.in')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb & Back --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.reports.index') }}" class="hover:text-white transition-colors">Laporan</a>
                <span>/</span>
                <span class="text-rose-400 font-mono font-bold">#{{ $report->id }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Detail Laporan Pelanggaran
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center gap-2">
            <span>✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs space-y-1">
            <div class="font-bold">Terjadi kesalahan validasi:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Susunan Detail Sesuai Aturan (Atas ke Bawah):
         1. Identitas Pelapor dan Terlapor
         2. Badge Status Read-only
         3. Alasan, Deskripsi, dan Tanggal
         4. Bukti
         5. Riwayat & Keputusan
         6. Tombol Aksi (Tolak, Selesai, dan Sanksi) --}}

    {{-- 1. IDENTITAS PELAPOR & TERLAPOR --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card Pelapor (Hanya untuk Admin, identitas aman dan tidak tampil ke terlapor) --}}
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Identitas Pelapor (Internal Admin)</h4>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 font-mono">Pelapor</span>
            </div>

            @if($report->reporter)
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-extrabold flex items-center justify-center text-sm shadow-md flex-shrink-0">
                        {{ strtoupper(substr($report->reporter->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="font-bold text-white text-sm truncate">{{ $report->reporter->name }}</div>
                        <div class="text-xs text-slate-400 font-mono truncate">{{ $report->reporter->email }}</div>
                        <div class="text-[11px] text-slate-400">
                            Username: <span class="text-slate-300 font-mono font-semibold">{{ '@' . ($report->reporter->username ?? '-') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Role: <span class="capitalize px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-semibold">{{ $report->reporter->role }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-xs text-slate-400 italic">Akun pelapor telah dihapus dari sistem.</div>
            @endif
        </div>

        {{-- Card Terlapor (Polymorphic: User / Community) --}}
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Entitas yang Dilaporkan</h4>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-mono uppercase font-bold">
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
                        <div class="w-11 h-11 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-lg flex-shrink-0">
                            🏟️
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="font-bold text-white text-sm truncate">{{ $target->name }}</div>
                            <div class="text-xs text-slate-400">Kategori: <span class="text-slate-300 font-medium capitalize">{{ $target->sport_category }}</span></div>
                            <div class="text-[11px] text-slate-400">Kota: <span class="text-slate-300">{{ $target->city ?? '-' }}</span></div>
                            <div class="text-[11px] text-slate-400">
                                Status Aktif:
                                <span class="font-semibold {{ $target->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $target->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @elseif($targetType === 'User')
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-600 to-orange-500 text-white font-extrabold flex items-center justify-center text-sm shadow-md flex-shrink-0">
                            {{ strtoupper(substr($target->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="font-bold text-white text-sm truncate">{{ $target->name }}</div>
                            <div class="text-xs text-slate-400 font-mono truncate">{{ $target->email }}</div>
                            <div class="text-[11px] text-slate-400">Role: <span class="capitalize text-slate-300">{{ $target->role }}</span></div>
                            <div class="text-[11px] text-slate-400">
                                Status Akun:
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

    {{-- 2 & 3. BADGE STATUS READ-ONLY, ALASAN, DESKRIPSI, DAN TANGGAL --}}
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-lg font-bold">
                    ⚠️
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-bold text-white">{{ $report->reason }}</h3>
                        {{-- 2. BADGE READ-ONLY BERWARNA (SATU SUMBER MODEL REPORT) --}}
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $report->status_badge_class }}">
                            {{ $report->status_label }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">
                        Dilaporkan pada {{ $report->created_at->translatedFormat('d F Y, H:i') }} WIB ({{ $report->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            @if(in_array($report->status, ['resolved', 'dismissed']))
                <span class="px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-[11px] font-semibold">
                    🔒 Status Final (Terkunci)
                </span>
            @endif
        </div>

        {{-- 3. Deskripsi Pelanggaran --}}
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi / Kronologi Laporan</label>
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                {{ $report->description ?: 'Tidak ada deskripsi rinci yang disertakan oleh pelapor.' }}
            </div>
        </div>

        {{-- 4. BUKTI (EVIDENCE) --}}
        <div class="space-y-2 pt-2">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Bukti Lampiran / Tautan Pendukung</label>
            @if($report->evidence)
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs text-slate-300 truncate">
                        <span>🔗</span>
                        <span class="font-mono truncate">{{ $report->evidence }}</span>
                    </div>
                    <a href="{{ $report->evidence }}" target="_blank" rel="noopener noreferrer"
                       class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition-colors flex-shrink-0 flex items-center gap-1.5 shadow-sm shadow-blue-500/20">
                        <span>Buka Bukti</span>
                        <span>&nearr;</span>
                    </a>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-950/50 border border-dashed border-slate-800 text-xs text-slate-500 italic">
                    Pelapor tidak menyertakan tautan bukti pendukung tambahan.
                </div>
            @endif
        </div>
    </div>

    {{-- 5. RIWAYAT & KEPUTUSAN PENANGANAN --}}
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Riwayat &amp; Keputusan Penanganan</h4>
            @if($report->handled_by)
                <span class="text-[10px] font-mono text-slate-400">
                    Selesai ditangani {{ $report->handled_at ? $report->handled_at->diffForHumans() : '-' }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-1">
                <span class="text-[11px] text-slate-400 font-medium">Admin Penanggung Jawab:</span>
                <div class="font-bold text-white text-sm">
                    {{ $report->handler->name ?? ($report->handled_by ? 'Admin #' . $report->handled_by : 'Belum Ditangani') }}
                </div>
                @if($report->handler && $report->handler->email)
                    <div class="text-[10px] text-slate-400 font-mono">{{ $report->handler->email }}</div>
                @endif
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-1">
                <span class="text-[11px] text-slate-400 font-medium">Waktu Keputusan Final:</span>
                <div class="font-bold text-white text-sm">
                    {{ $report->handled_at ? $report->handled_at->translatedFormat('d F Y, H:i') . ' WIB' : 'Belum Diputuskan' }}
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                    {{ $report->handled_at ? $report->handled_at->diffForHumans() : 'Menunggu tindakan final' }}
                </div>
            </div>
        </div>

        {{-- Keputusan: Rejection Reason (Jika ditolak) --}}
        @if($report->status === 'dismissed' && ($report->rejection_reason || $report->admin_notes))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs space-y-1">
                <span class="font-bold text-rose-300 block">Alasan Penolakan Laporan (Terlihat oleh Pelapor):</span>
                <p class="text-slate-200 leading-relaxed whitespace-pre-line">{{ $report->rejection_reason ?: $report->admin_notes }}</p>
            </div>
        @endif

        {{-- Keputusan: Admin Notes (Catatan internal) --}}
        @if($report->admin_notes)
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 text-xs space-y-1">
                <span class="font-bold text-slate-300 block">Catatan Internal Admin (Hanya terbaca oleh Admin):</span>
                <p class="text-slate-300 leading-relaxed whitespace-pre-line">{{ $report->admin_notes }}</p>
            </div>
        @endif
    </div>

    {{-- 6. TOMBOL AKSI: TOLAK, SELESAI, DAN QUICK ACTION SANKSI --}}
    @php
        $isFinal = in_array($report->status, ['resolved', 'dismissed']);
    @endphp

    @if(!$isFinal)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="{ showDismissModal: false, showSancModal: false }">

            {{-- Kolom Aksi Keputusan: Selesaikan & Tolak --}}
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                <div class="pb-3 border-b border-slate-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Keputusan Status Laporan</h4>
                    <p class="text-xs text-slate-400 mt-1">Pilih keputusan akhir untuk tiket laporan ini.</p>
                </div>

                {{-- Form Selesaikan Laporan (Resolved) --}}
                <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="admin_notes_resolve" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Catatan Internal Admin (Opsional, maks. 1000 karakter)
                        </label>
                        <textarea id="admin_notes_resolve" name="admin_notes" rows="2"
                                  placeholder="Tuliskan catatan investigasi atau hasil tindakan (internal admin)..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-emerald-500 transition-colors resize-none"></textarea>
                    </div>

                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin menyelesaikan laporan ini?')"
                            class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>✓ Selesaikan Laporan (Resolved)</span>
                    </button>
                </form>

                <div class="pt-3 border-t border-slate-800">
                    {{-- Tombol Buka Modal Tolak Laporan --}}
                    <button type="button"
                            @click="showDismissModal = true"
                            class="w-full py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>✕ Tolak Laporan (Dismissed)...</span>
                    </button>
                </div>
            </div>

            {{-- Kolom Tindakan Sanksi Administratif (Quick Action AD-03 / AD-04) --}}
            @if($report->reportable)
                @php
                    $isCommunity = class_basename($report->reportable_type) === 'Community';
                    $isUser = class_basename($report->reportable_type) === 'User';
                    $targetObj = $report->reportable;
                @endphp

                @if($isCommunity || $isUser)
                    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                        <div class="pb-3 border-b border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">
                                Sanksi Administratif {{ $isCommunity ? 'Komunitas' : 'Akun Pengguna' }}
                            </h4>
                            <p class="text-xs text-slate-400 mt-1">
                                Status saat ini:
                                <strong class="{{ $targetObj->is_active ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $targetObj->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </strong>
                            </p>
                        </div>

                        <form method="POST"
                              action="{{ $isCommunity ? route('admin.reports.community.toggle-status', $targetObj->id) : route('admin.reports.user.toggle-status', $targetObj->id) }}"
                              class="space-y-3">
                            @csrf
                            <input type="hidden" name="report_id" value="{{ $report->id }}">

                            @if($targetObj->is_active)
                                <div>
                                    <label for="sanc_reason" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                        Alasan Sanksi Penonaktifan (Wajib, min 5 karakter) <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea id="sanc_reason" name="reason" rows="2" required minlength="5" maxlength="1000"
                                              placeholder="Jelaskan alasan penonaktifan berdasarkan hasil investigasi laporan..."
                                              class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-rose-500 transition-colors resize-none"></textarea>
                                </div>
                            @endif

                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin memproses perubahan status aktif entitas ini?')"
                                    class="w-full py-2.5 rounded-xl {{ $targetObj->is_active ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-600/20' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20' }} font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ $targetObj->is_active ? 'Nonaktifkan Sementara (Beri Sanksi)' : 'Aktifkan Kembali Entitas' }}</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endif

            {{-- MODAL TOLAK LAPORAN (DISMISSED) --}}
            <div x-show="showDismissModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
                 style="display: none;"
                 x-transition>
                <div @click.away="showDismissModal = false"
                     class="w-full max-w-md p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">✕</span>
                            <h3 class="text-sm font-bold text-white">Tolak Laporan #{{ $report->id }}</h3>
                        </div>
                        <button type="button" @click="showDismissModal = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="rejection_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Alasan Penolakan <span class="text-rose-400">* (Wajib, 10–1000 karakter)</span>
                            </label>
                            <p class="text-[11px] text-slate-400 mb-2">
                                Alasan ini akan ditampilkan secara transparan kepada pelapor di Riwayat Laporan mereka.
                            </p>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" required minlength="10" maxlength="1000"
                                      placeholder="Contoh: Bukti yang dilampirkan tidak mencukupi atau tidak relevan dengan pelanggaran..."
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-rose-500 transition-colors resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button"
                                    @click="showDismissModal = false"
                                    class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                                Konfirmasi Tolak Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    @else
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-400">
            Laporan ini sudah diselesaikan dengan status <strong class="text-white uppercase">{{ $report->status_label }}</strong>. Seluruh aksi perubahan keputusan atau penjatuhan sanksi telah dikunci.
        </div>
    @endif

</div>
@endsection
