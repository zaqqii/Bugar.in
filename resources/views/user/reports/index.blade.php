@extends('layouts.user')

@section('title', 'Riwayat Laporan — Bugar.in')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Navigasi ringkas --}}
    <div class="flex items-center justify-between text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Riwayat laporan</span>
        </div>
        <a href="{{ route('user.dashboard') }}" class="text-slate-600 hover:text-slate-900 font-medium inline-flex items-center gap-1">
            <span>&larr;</span>
            <span>Kembali</span>
        </a>
    </div>

    {{-- Header ringkas --}}
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs flex items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                Riwayat laporan
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Status dan tindak lanjut aduan pelanggaran yang pernah Anda ajukan.
            </p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0112 3.464z" />
            </svg>
        </div>
    </div>

    {{-- Daftar laporan --}}
    <div class="space-y-3.5">
        @forelse($reports as $report)
            @php
                $targetName = '-';
                $targetType = class_basename($report->reportable_type);
                if ($report->reportable) {
                    $targetName = $report->reportable->name ?? ($report->reportable->title ?? ('#' . $report->reportable_id));
                }

                $statusLabel = match($report->status) {
                    'pending' => 'Belum dicek',
                    'under_review' => 'Sedang ditinjau',
                    'resolved' => 'Selesai',
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
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-3 hover:border-slate-300 transition-colors">
                
                {{-- Header kartu: Target & Status --}}
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] uppercase font-mono font-bold flex-shrink-0">
                            {{ $targetType === 'Community' ? 'Komunitas' : ($targetType === 'User' ? 'Akun' : $targetType) }}
                        </span>
                        <span class="font-bold text-slate-900 truncate">
                            {{ $targetName }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono hidden sm:inline">
                            {{ $report->created_at->translatedFormat('d M Y') }}
                        </span>
                    </div>
                </div>

                {{-- Konten: Alasan & Deskripsi --}}
                <div class="space-y-1.5 text-xs">
                    <div class="font-semibold text-slate-800">
                        {{ $report->reason }}
                    </div>
                    <p class="text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 line-clamp-3">
                        {{ $report->description }}
                    </p>
                </div>

                {{-- Berkas bukti (Link & Foto) --}}
                @if($report->evidence || (!empty($report->evidence_images) && is_array($report->evidence_images)))
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-400 text-[11px]">Bukti:</span>

                        {{-- Tautan bukti --}}
                        @if($report->evidence)
                            <a href="{{ $report->evidence }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium text-[11px] transition-colors">
                                <span>Tautan</span>
                                <span>&nearr;</span>
                            </a>
                        @endif

                        {{-- Foto bukti yang pernah diunggah (cukup nama file) --}}
                        @if(!empty($report->evidence_images) && is_array($report->evidence_images))
                            @foreach($report->evidence_images as $imgPath)
                                @php
                                    $imgName = basename($imgPath);
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-mono border border-slate-200 truncate max-w-[150px]" title="{{ $imgName }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <span class="truncate">{{ $imgName }}</span>
                                </span>
                            @endforeach
                        @endif
                    </div>
                @endif

                {{-- Status tindak lanjut admin --}}
                @if($report->status === 'resolved')
                    <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200/60 text-xs text-emerald-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span class="font-medium">Laporan telah diselesaikan dan ditindaklanjuti.</span>
                    </div>
                @elseif($report->status === 'dismissed')
                    @php
                        $userReason = $report->rejection_reason ?: $report->admin_notes;
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-700 space-y-1">
                        <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Laporan ditutup oleh admin</span>
                        </div>
                        @if($userReason)
                            <p class="text-slate-600 text-[11px] pl-5">
                                <span class="font-medium text-slate-700">Alasan penolakan:</span> {{ $userReason }}
                            </p>
                        @endif
                    </div>
                @elseif($report->status === 'under_review')
                    <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-200/60 text-xs text-blue-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Sedang ditinjau oleh pengelola komunitas &amp; tim admin.</span>
                    </div>
                @else
                    <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-xs text-amber-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Menunggu peninjauan antrean laporan.</span>
                    </div>
                @endif

            </div>
        @empty
            <div class="rounded-2xl bg-white border border-dashed border-slate-200 p-10 text-center space-y-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-slate-800">Belum ada riwayat laporan</h3>
                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                    Semua aduan pelanggaran yang Anda kirim akan tercatat di sini.
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="pt-3">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
