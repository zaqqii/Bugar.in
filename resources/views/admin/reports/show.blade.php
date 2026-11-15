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

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Kolom Kiri: Informasi Laporan, Pelapor, Terlapor & Bukti (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. Informasi Utama Laporan --}}
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-lg font-bold">
                            ⚠️
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-white">{{ $report->reason }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $report->status === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' :
                                       ($report->status === 'under_review' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' :
                                       ($report->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' :
                                       'bg-slate-700 text-slate-300 border border-slate-600')) }}">
                                    {{ str_replace('_', ' ', $report->status) }}
                                </span>
                            </div>
                            <span class="text-xs text-slate-400 font-mono">Dilaporkan pada {{ $report->created_at->translatedFormat('d F Y, H:i') }} WIB ({{ $report->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi Pelanggaran --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi / Kronologi Laporan</label>
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $report->description ?: 'Tidak ada deskripsi rinci yang disertakan oleh pelapor.' }}
                    </div>
                </div>

                {{-- Bukti Pendukung / Evidence --}}
                <div class="space-y-2">
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

            {{-- 2. Detail Pihak Terkait: Pelapor & Terlapor --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Card Pelapor (Hanya untuk Admin, identitas aman) --}}
                <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Identitas Pelapor (Rahasia)</h4>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 font-mono">Reporter</span>
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

        </div>

        {{-- Kolom Kanan: Aksi Penanganan Status & Tindakan Lanjutan (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Form Penanganan Status --}}
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
                <div class="pb-3 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Tindakan Admin & Status</h3>
                    <p class="text-xs text-slate-400 mt-1">Ubah status penyelesaian dan berikan catatan investigasi.</p>
                </div>

                <form method="POST" action="{{ route('admin.reports.update-status', $report->id) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            Status Laporan
                        </label>
                        <select id="status" name="status"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs font-semibold focus:outline-hidden focus:border-blue-500 transition-colors">
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>⏳ Pending (Menunggu Ditinjau)</option>
                            <option value="under_review" {{ $report->status === 'under_review' ? 'selected' : '' }}>🔍 Under Review (Sedang Diinvestigasi)</option>
                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>✅ Resolved (Diselesaikan/Diberi Sanksi)</option>
                            <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>❌ Dismissed (Ditolak / Tidak Valid)</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            Catatan Tindak Lanjut Admin
                        </label>
                        <textarea id="admin_notes" name="admin_notes" rows="4"
                                  placeholder="Tuliskan hasil investigasi atau alasan keputusan yang diambil..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs placeholder:text-slate-600 focus:outline-hidden focus:border-blue-500 transition-colors resize-none">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Status &amp; Catatan</span>
                    </button>
                </form>

                {{-- Riwayat Penanganan --}}
                @if($report->handled_by)
                    <div class="pt-4 border-t border-slate-800/80 space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Riwayat Penanganan</span>
                        <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs space-y-1">
                            <div class="text-slate-300">
                                Ditangani oleh: <span class="font-bold text-white">{{ $report->handler->name ?? 'Admin' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                Waktu: {{ $report->handled_at ? $report->handled_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tindakan Lanjutan terhadap Entitas Terlapor --}}
            @if($report->reportable)
                @php
                    $isCommunity = class_basename($report->reportable_type) === 'Community';
                    $isUser = class_basename($report->reportable_type) === 'User';
                    $targetObj = $report->reportable;
                @endphp

                @if($isCommunity || $isUser)
                    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300 block">
                            Tindakan Sanksi {{ $isCommunity ? 'Komunitas' : 'Pengguna' }}
                        </span>
                        <p class="text-[11px] text-slate-400">
                            Status saat ini: <strong class="{{ $targetObj->is_active ? 'text-emerald-400' : 'text-rose-400' }}">{{ $targetObj->is_active ? 'Aktif' : 'Dinonaktifkan' }}</strong>
                        </p>

                        <form method="POST"
                              action="{{ $isCommunity ? route('admin.reports.community.toggle-status', $targetObj->id) : route('admin.reports.user.toggle-status', $targetObj->id) }}">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin mengubah status aktif entitas ini?')"
                                    class="w-full py-2.5 rounded-xl {{ $targetObj->is_active ? 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }} text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ $targetObj->is_active ? 'Nonaktifkan / Bekukan' : 'Aktifkan Kembali' }}</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endif

        </div>

    </div>

</div>
@endsection
