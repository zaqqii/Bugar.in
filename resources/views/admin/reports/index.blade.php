@extends('layouts.admin')

@section('title', 'Pusat laporan & moderasi — Console admin Bugar.in')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-rose-400 font-bold">Laporan pengguna</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                Pusat moderasi &amp; laporan
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Kelola aduan pelanggaran dari pengguna terhadap komunitas dan akun.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.dashboard') }}"
               class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-800 transition-all inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke dashboard</span>
            </a>
        </div>
    </div>

    {{-- Filter toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-3">
        {{-- Filter status tabs --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.reports.index', array_filter(['type' => $type])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ empty($status) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'pending', 'type' => $type])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-amber-400/80 hover:text-amber-300 border border-slate-800' }}">
                Belum dicek ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'under_review', 'type' => $type])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'under_review' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-900 text-blue-400/80 hover:text-blue-300 border border-slate-800' }}">
                Sedang ditinjau ({{ $counts['under_review'] }})
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'resolved', 'type' => $type])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'resolved' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-900 text-emerald-400/80 hover:text-emerald-300 border border-slate-800' }}">
                Selesai ({{ $counts['resolved'] }})
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'dismissed', 'type' => $type])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'dismissed' ? 'bg-slate-700 text-white font-bold' : 'bg-slate-900 text-slate-400 hover:text-slate-300 border border-slate-800' }}">
                Ditolak ({{ $counts['dismissed'] }})
            </a>
        </div>

        {{-- Filter tipe target --}}
        <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800 text-xs flex-shrink-0">
            <a href="{{ route('admin.reports.index', array_filter(['status' => $status])) }}"
               class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ empty($type) ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Semua target
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => $status, 'type' => 'community'])) }}"
               class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $type === 'community' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Komunitas
            </a>
            <a href="{{ route('admin.reports.index', array_filter(['status' => $status, 'type' => 'user'])) }}"
               class="px-2.5 py-1 rounded-lg font-semibold transition-all {{ $type === 'user' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Pengguna
            </a>
        </div>
    </div>

    {{-- Tabel laporan --}}
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/50 text-slate-400 uppercase tracking-wider font-bold text-[10px]">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Alasan &amp; kronologi</th>
                        <th class="py-3 px-4">Pelapor</th>
                        <th class="py-3 px-4">Terlapor</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($reports as $r)
                        @php
                            $targetName = '-';
                            $targetType = class_basename($r->reportable_type);
                            if ($r->reportable) {
                                $targetName = $r->reportable->name ?? ($r->reportable->title ?? ('#' . $r->reportable_id));
                            }
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-rose-400">
                                #{{ $r->id }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-white text-xs truncate">{{ $r->reason }}</div>
                                <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                    {{ $r->description ?: 'Tanpa deskripsi tambahan' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-200">{{ $r->reporter->name ?? 'User dihapus' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $r->reporter->email ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-slate-300 mb-0.5 inline-block">
                                    {{ $targetType === 'Community' ? 'Komunitas' : ($targetType === 'User' ? 'Pengguna' : $targetType) }}
                                </span>
                                <div class="font-semibold text-white truncate max-w-[140px]">{{ $targetName }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $r->status_badge_class }}">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                                {{ $r->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.reports.show', $r->id) }}"
                                   class="px-3 py-1.5 rounded-xl bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-500/20 font-bold text-xs transition-all inline-flex items-center gap-1.5">
                                    <span>Periksa</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Tidak ada laporan yang ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="p-3.5 border-t border-slate-800">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
