@extends('layouts.penyedia-komunitas')

@section('title', 'Detail Laporan #' . $report->id . ' — Penyedia Komunitas')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{ showDismissModal: false, showForwardModal: false }">

    {{-- Breadcrumb & Back --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('penyedia-komunitas.dashboard') }}" class="hover:text-primary-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('penyedia-komunitas.laporan.index') }}" class="hover:text-primary-600 transition-colors">Laporan anggota</a>
                <span>/</span>
                <span class="text-rose-600 font-mono font-bold">#{{ $report->id }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Detail Laporan Pelanggaran Anggota
            </h1>
        </div>

        <div>
            <a href="{{ route('penyedia-komunitas.laporan.index') }}"
               class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 transition-all inline-flex items-center gap-2 shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke daftar</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Kartu Utama Informasi Laporan --}}
    <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6">

        {{-- Header Status & Waktu --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $report->reason }}</h3>
                    <span class="text-xs text-slate-400 font-mono">
                        Dilaporkan pada {{ $report->created_at->translatedFormat('d F Y, H:i') }} WIB ({{ $report->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            @php
                $statusLabel = match($report->status) {
                    'pending' => 'Belum dicek',
                    'under_review' => 'Sedang ditinjau',
                    'resolved' => 'Disetujui / Diajukan sanksi',
                    'dismissed' => 'Ditolak',
                    default => ucfirst(str_replace('_', ' ', $report->status))
                };
                $badgeClass = match($report->status) {
                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'under_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'dismissed' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200'
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                {{ $statusLabel }}
            </span>
        </div>

        {{-- Pihak Terkait: Pelapor & Terlapor --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pelapor</span>
                <div class="font-bold text-slate-900 text-sm">{{ $report->reporter->name ?? 'User dihapus' }}</div>
                <div class="text-xs text-slate-500 font-mono">{{ $report->reporter->email ?? '-' }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">Anggota Terlapor</span>
                <div class="font-bold text-slate-900 text-sm">{{ $report->reportable->name ?? 'User dihapus' }}</div>
                <div class="text-xs text-slate-500 font-mono">{{ $report->reportable->email ?? '-' }}</div>
            </div>
        </div>

        {{-- Kronologi & Deskripsi --}}
        <div class="space-y-1.5">
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Kronologi / Uraian Laporan</label>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-800 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                {{ $report->description ?: 'Tidak ada uraian rinci yang disertakan.' }}
            </div>
        </div>

        {{-- Bukti Tautan / Foto --}}
        <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Bukti Lampiran</label>
            @if($report->evidence)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <span class="text-slate-600 truncate font-mono">{{ $report->evidence }}</span>
                    <a href="{{ $report->evidence }}" target="_blank" rel="noopener noreferrer"
                       class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs flex-shrink-0 transition-colors inline-flex items-center gap-1">
                        <span>Buka tautan</span>
                        <span>&nearr;</span>
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
                        <a href="{{ $fullUrl }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 group relative">
                            <img src="{{ $fullUrl }}" alt="Bukti laporan" class="w-full h-24 object-cover group-hover:scale-105 transition-transform duration-200">
                            <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-semibold">
                                Perbesar
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(!$report->evidence && empty($evidenceImages))
                <div class="p-4 rounded-2xl bg-slate-50/60 border border-dashed border-slate-200 text-xs text-slate-400 italic">
                    Pelapor tidak menyertakan tautan atau foto bukti tambahan.
                </div>
            @endif
        </div>

        {{-- Catatan Tindakan / Peringatan Sebelumnya --}}
        @if($report->action_note)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/70 text-xs space-y-1">
                <span class="font-bold text-amber-800 block">Riwayat catatan / eskalasi:</span>
                <p class="text-slate-700 leading-relaxed whitespace-pre-line font-mono text-[11px]">{{ $report->action_note }}</p>
            </div>
        @endif

        {{-- Tombol Aksi Pengelola (Hanya jika belum status final) --}}
        @php
            $isFinal = in_array($report->status, ['resolved', 'dismissed']);
        @endphp

        @if(!$isFinal)
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3">
                {{-- Tombol Tolak --}}
                <button type="button"
                        @click="showDismissModal = true"
                        class="w-full sm:flex-1 py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Tolak laporan (tidak terbukti)</span>
                </button>

                {{-- Tombol Setujui & Ajukan Permintaan Nonaktif ke Admin --}}
                <button type="button"
                        @click="showForwardModal = true"
                        class="w-full sm:flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>Setujui &amp; ajukan penonaktifan akun ke admin</span>
                </button>
            </div>
        @else
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-500 text-center font-medium">
                Laporan ini sudah berada dalam status final dan telah selesai diproses.
            </div>
        @endif

    </div>

    {{-- MODAL 1: TOLAK LAPORAN --}}
    <div x-show="showDismissModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-2xs"
         style="display: none;">

        <div @click.away="showDismissModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md p-6 rounded-3xl bg-white shadow-2xl border border-slate-100 space-y-4">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Tolak laporan #{{ $report->id }}</h3>
                </div>
                <button type="button" @click="showDismissModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('penyedia-komunitas.laporan.dismiss', $report->id) }}" class="space-y-3.5">
                @csrf
                <div>
                    <label for="dismiss_reason" class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan penolakan <span class="text-rose-500">* (min. 10 karakter)</span>
                    </label>
                    <textarea id="dismiss_reason" name="reason" rows="3" required minlength="10" maxlength="1000"
                              placeholder="Jelaskan mengapa laporan ini tidak terbukti atau tidak memenuhi kriteria pelanggaran..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs placeholder:text-slate-400 focus:outline-hidden focus:border-primary-600 focus:bg-white transition-colors resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showDismissModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition-colors cursor-pointer shadow-xs">
                        Tolak laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: AJUKAN PERMINTAAN PENONAKTIFAN AKUN KE ADMIN --}}
    <div x-show="showForwardModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-2xs"
         style="display: none;">

        <div @click.away="showForwardModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md p-6 rounded-3xl bg-white shadow-2xl border border-slate-100 space-y-4">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Ajukan sanksi akun ke admin</h3>
                </div>
                <button type="button" @click="showForwardModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('penyedia-komunitas.laporan.forward', $report->id) }}" class="space-y-3.5">
                @csrf
                <p class="text-xs text-slate-600 leading-relaxed">
                    Anda akan menyetujui laporan ini dan mengajukan permintaan tindakan penonaktifan akun untuk <strong class="text-slate-900">{{ $report->reportable->name ?? 'anggota' }}</strong> ke tim Admin Platform Bugar.in.
                </p>

                <div>
                    <label for="request_note" class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan alasan permintaan sanksi <span class="text-rose-500">* (min. 10 karakter)</span>
                    </label>
                    <textarea id="request_note" name="request_note" rows="3" required minlength="10" maxlength="1000"
                              placeholder="Tuliskan alasan dan bukti yang mendasari perlunya akun pengguna ini dinonaktifkan..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs placeholder:text-slate-400 focus:outline-hidden focus:border-rose-600 focus:bg-white transition-colors resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="showForwardModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition-colors cursor-pointer shadow-xs">
                        Kirim permohonan ke admin
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
