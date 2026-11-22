@forelse($auditLogs as $log)
    @php
        $targetName = '-';
        $targetType = class_basename($log->auditable_type);
        if ($log->auditable) {
            $targetName = $log->auditable->name ?? ($log->auditable->title ?? ('#' . $log->auditable_id));
        }

        // Warna & Icon badge aksi
        $actionClass = 'bg-slate-800 text-slate-300 border-slate-700';
        $actionType = 'default';
        if (str_contains($log->action, 'verify') || str_contains($log->action, 'active')) {
            $actionClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
            $actionType = 'success';
        } elseif (str_contains($log->action, 'reject') || str_contains($log->action, 'suspend') || str_contains($log->action, 'deactive')) {
            $actionClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
            $actionType = 'danger';
        } elseif (str_contains($log->action, 'report') || str_contains($log->action, 'warn')) {
            $actionClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
            $actionType = 'warning';
        }
    @endphp
    <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0 border {{ $actionClass }}">
                @if($actionType === 'success')
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @elseif($actionType === 'danger')
                    <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @elseif($actionType === 'warning')
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0112 3.464zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                @else
                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                @endif
            </div>
            <div class="min-w-0 space-y-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="font-bold text-white tracking-tight">
                        {{ $log->user->name ?? 'Sistem / admin' }}
                    </span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="font-mono text-[10px] px-2 py-0.5 rounded-md border font-semibold {{ $actionClass }}">
                        {{ $log->action }}
                    </span>
                </div>
                <p class="text-slate-400 text-[11px] leading-relaxed">
                    Target: <strong class="text-slate-200">{{ $targetName }}</strong>
                    <span class="text-slate-400 text-[10px] font-mono">({{ $targetType }} #{{ $log->auditable_id }})</span>
                </p>
                @if($log->new_values && isset($log->new_values['notes']))
                    <p class="text-[10px] text-slate-400 italic">"{{ Str::limit($log->new_values['notes'], 90) }}"</p>
                @elseif($log->new_values && isset($log->new_values['reason']))
                    <p class="text-[10px] text-slate-400 italic">"{{ Str::limit($log->new_values['reason'], 90) }}"</p>
                @elseif($log->new_values && isset($log->new_values['warning']))
                    <p class="text-[10px] text-slate-400 italic">"{{ Str::limit($log->new_values['warning'], 90) }}"</p>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-between sm:flex-col sm:items-end gap-1 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-800/60 text-[11px] text-slate-400 font-mono">
            <span>{{ $log->created_at->diffForHumans() }}</span>
            <span class="text-[10px] text-slate-400">{{ $log->created_at->translatedFormat('d M, H:i') }} WIB</span>
        </div>
    </div>
@empty
    <div class="p-8 text-center rounded-2xl bg-slate-950/40 border border-dashed border-slate-800 text-xs text-slate-400">
        Belum ada riwayat aktivitas audit yang tercatat.
    </div>
@endforelse

{{-- Pagination Ajax Links --}}
@if($auditLogs->hasPages())
    <div class="pt-3 flex items-center justify-between text-xs font-mono">
        <div>
            @if($auditLogs->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-slate-600 border border-slate-800/60 cursor-not-allowed inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <button type="button"
                        @click="fetchLogs('{{ $auditLogs->previousPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>
            @endif
        </div>

        <span class="text-slate-400 text-[11px]">
            Halaman {{ $auditLogs->currentPage() }} dari {{ $auditLogs->lastPage() }}
        </span>

        <div>
            @if($auditLogs->hasMorePages())
                <button type="button"
                        @click="fetchLogs('{{ $auditLogs->nextPageUrl() }}')"
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
