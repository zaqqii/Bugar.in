@extends('layouts.user')

@section('title', 'Riwayat Laporan Saya — Bugar.in')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">Riwayat Laporan</span>
    </div>

    {{-- Header Banner --}}
    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs relative overflow-hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-xl flex-shrink-0">
                🛡️
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Riwayat Laporan Pelanggaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Daftar aduan pelanggaran yang pernah Anda ajukan ke Tim Admin Bugar.in.
                </p>
            </div>
        </div>

        <div>
            <a href="{{ route('user.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>

    {{-- Daftar Laporan --}}
    <div class="space-y-4">
        @forelse($reports as $report)
            @php
                $targetName = '-';
                $targetType = class_basename($report->reportable_type);
                if ($report->reportable) {
                    $targetName = $report->reportable->name ?? ($report->reportable->title ?? ('#' . $report->reportable_id));
                }
            @endphp
            <div class="rounded-3xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4 hover:border-slate-300 transition-colors">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $report->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                               ($report->status === 'under_review' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                               ($report->status === 'resolved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                               'bg-slate-100 text-slate-600 border border-slate-200')) }}">
                            {{ str_replace('_', ' ', $report->status) }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            {{ $report->created_at->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </div>

                    <span class="text-xs font-mono text-slate-400">#{{ $report->id }}</span>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400">Terlapor:</span>
                        <span class="font-bold text-slate-800">
                            {{ $targetName }}
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] uppercase font-mono">
                            {{ $targetType }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $report->reason }}
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-2xl border border-slate-100 whitespace-pre-line">
                        {{ $report->description }}
                    </p>

                    @if($report->evidence)
                        <div class="pt-1 text-xs">
                            <span class="text-slate-400">Bukti Lampiran:</span>
                            <a href="{{ $report->evidence }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1 ml-1">
                                <span>Buka Tautan Bukti</span>
                                <span>&nearr;</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Status Tindak Lanjut dari Admin jika ada catatan --}}
                @if($report->status === 'resolved' || $report->status === 'dismissed')
                    <div class="p-3.5 rounded-2xl {{ $report->status === 'resolved' ? 'bg-emerald-50/70 border border-emerald-200/60' : 'bg-slate-50 border border-slate-200/60' }} text-xs space-y-1">
                        <strong class="font-bold block {{ $report->status === 'resolved' ? 'text-emerald-800' : 'text-slate-700' }}">
                            {{ $report->status === 'resolved' ? '✓ Laporan Telah Diselesaikan oleh Admin' : '✕ Laporan Ditutup / Ditolak oleh Admin' }}
                        </strong>
                        @if($report->admin_notes)
                            <p class="text-slate-600 italic">"{{ $report->admin_notes }}"</p>
                        @endif
                    </div>
                @elseif($report->status === 'under_review')
                    <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200/60 text-xs text-blue-800">
                        🔍 Laporan sedang dalam tahap peninjauan dan investigasi oleh Tim Moderasi Bugar.in.
                    </div>
                @else
                    <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-xs text-amber-800">
                        ⏳ Menunggu antrean peninjauan oleh Admin.
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white border border-dashed border-slate-200 p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                    ✓
                </div>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Riwayat Laporan</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Anda belum pernah mengajukan aduan pelanggaran. Kami mengapresiasi kontribusi Anda dalam menjaga komunitas tetap nyaman.
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="pt-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
