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
    <div class="p-3.5 border-t border-slate-800 flex items-center justify-between text-xs font-mono">
        <div>
            @if($reports->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-slate-600 border border-slate-800/60 cursor-not-allowed inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <button type="button"
                        @click="fetchReports('{{ $reports->previousPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>
            @endif
        </div>

        <span class="text-slate-400 text-[11px]">
            Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}
        </span>

        <div>
            @if($reports->hasMorePages())
                <button type="button"
                        @click="fetchReports('{{ $reports->nextPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-slate-600 border border-slate-800/60 cursor-not-allowed inline-flex items-center gap-1.5">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            @endif
        </div>
    </div>
@endif
