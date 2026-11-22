<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-xs">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Alasan &amp; kronologi</th>
                <th class="py-3 px-4">Pelapor</th>
                <th class="py-3 px-4">Anggota terlapor</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Waktu</th>
                <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($reports as $r)
                @php
                    $targetUser = $r->reportable;
                    $statusLabel = match($r->status) {
                        'pending' => 'Belum dicek',
                        'under_review' => 'Sedang ditinjau',
                        'resolved' => 'Disetujui / Sanksi',
                        'dismissed' => 'Ditolak',
                        default => ucfirst(str_replace('_', ' ', $r->status))
                    };

                    $badgeClass = match($r->status) {
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'under_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'dismissed' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-slate-100 text-slate-600 border-slate-200'
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="py-3.5 px-4 font-mono font-bold text-rose-600">
                        #{{ $r->id }}
                    </td>
                    <td class="py-3.5 px-4 max-w-xs">
                        <div class="font-bold text-slate-900 text-xs truncate">{{ $r->reason }}</div>
                        <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                            {{ $r->description ?: 'Tanpa deskripsi tambahan' }}
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-semibold text-slate-800">{{ $r->reporter->name ?? 'User dihapus' }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ $r->reporter->email ?? '-' }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-wider bg-slate-100 text-slate-600 mb-0.5 inline-block">
                            Anggota
                        </span>
                        <div class="font-semibold text-slate-900 truncate max-w-[140px]">{{ $targetUser->name ?? ('#' . $r->reportable_id) }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                        {{ $r->created_at->diffForHumans() }}
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <a href="{{ route('penyedia-komunitas.laporan.show', $r->id) }}"
                           class="px-3 py-1.5 rounded-xl bg-primary-50 hover:bg-primary-600 text-primary-700 hover:text-white border border-primary-200 font-bold text-xs transition-all inline-flex items-center gap-1.5">
                            <span>Tinjau</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-slate-400">
                        Tidak ada laporan anggota yang ditemukan untuk kriteria ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($reports->hasPages())
    <div class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-mono">
        <div>
            @if($reports->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-slate-400 border border-slate-200 cursor-not-allowed inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <button type="button"
                        @click="fetchReports('{{ $reports->previousPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>
            @endif
        </div>

        <span class="text-slate-500 text-[11px]">
            Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}
        </span>

        <div>
            @if($reports->hasMorePages())
                <button type="button"
                        @click="fetchReports('{{ $reports->nextPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-slate-400 border border-slate-200 cursor-not-allowed inline-flex items-center gap-1.5">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            @endif
        </div>
    </div>
@endif
