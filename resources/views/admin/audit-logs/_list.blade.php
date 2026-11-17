@forelse($auditLogs as $log)
    @php
        $targetName = '-';
        $targetType = class_basename($log->auditable_type);
        if ($log->auditable) {
            $targetName = $log->auditable->name ?? ($log->auditable->title ?? ('#' . $log->auditable_id));
        }

        // Warna & Icon badge aksi
        $actionClass = 'bg-slate-800 text-slate-300 border-slate-700';
        $actionIcon = '⚡';
        if (str_contains($log->action, 'verify') || str_contains($log->action, 'active')) {
            $actionClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
            $actionIcon = '✓';
        } elseif (str_contains($log->action, 'reject') || str_contains($log->action, 'suspend') || str_contains($log->action, 'deactive')) {
            $actionClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
            $actionIcon = '✕';
        } elseif (str_contains($log->action, 'report')) {
            $actionClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
            $actionIcon = '🛡️';
        }
    @endphp
    <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0 border {{ $actionClass }}">
                {{ $actionIcon }}
            </div>
            <div class="min-w-0 space-y-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="font-bold text-white tracking-tight">
                        {{ $log->user->name ?? 'Sistem / Admin' }}
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
                <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-slate-600 border border-slate-800/60 cursor-not-allowed">
                    &larr; Prev
                </span>
            @else
                <button type="button"
                        @click="fetchLogs('{{ $auditLogs->previousPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors cursor-pointer">
                    &larr; Prev
                </button>
            @endif
        </div>

        <span class="text-slate-400 text-[11px]">
            Hal. {{ $auditLogs->currentPage() }} dari {{ $auditLogs->lastPage() }}
        </span>

        <div>
            @if($auditLogs->hasMorePages())
                <button type="button"
                        @click="fetchLogs('{{ $auditLogs->nextPageUrl() }}')"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors cursor-pointer">
                    Next &rarr;
                </button>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-slate-600 border border-slate-800/60 cursor-not-allowed">
                    Next &rarr;
                </span>
            @endif
        </div>
    </div>
@endif
