@extends('layouts.admin')

@section('title', 'Audit Log Integritas — Console Admin Bugar.in')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-cyan-400 font-bold">Audit Log</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Pusat Rekaman Audit &amp; Keamanan
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Jejak audit permanen atas seluruh tindakan administratif dan operasional sensitif di platform Bugar.in.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.dashboard') }}"
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label for="filter-action" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                    Filter Aksi
                </label>
                <select id="filter-action" name="action" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs focus:outline-hidden focus:border-cyan-500">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-actor" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                    Filter Pelaku
                </label>
                <select id="filter-actor" name="user_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs focus:outline-hidden focus:border-cyan-500">
                    <option value="">Semua Pelaku</option>
                    @foreach($actors as $actor)
                        <option value="{{ $actor->id }}" {{ request('user_id') == $actor->id ? 'selected' : '' }}>
                            {{ $actor->name }} ({{ $actor->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-target" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                    Target Entitas
                </label>
                <select id="filter-target" name="target" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs focus:outline-hidden focus:border-cyan-500">
                    <option value="">Semua Target</option>
                    <option value="community" {{ request('target') === 'community' ? 'selected' : '' }}>Komunitas (Community)</option>
                    <option value="user" {{ request('target') === 'user' ? 'selected' : '' }}>Pengguna (User)</option>
                    <option value="report" {{ request('target') === 'report' ? 'selected' : '' }}>Laporan (Report)</option>
                </select>
            </div>

            <div>
                <label for="filter-date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                    Tanggal
                </label>
                <input type="date" id="filter-date" name="date" value="{{ request('date') }}"
                       class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs focus:outline-hidden focus:border-cyan-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 py-2 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-md shadow-cyan-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['action', 'user_id', 'target', 'date']))
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel / List Audit Log --}}
    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/50 text-slate-400 uppercase tracking-wider font-bold text-[10px]">
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Pelaku</th>
                        <th class="py-3.5 px-4">Aksi</th>
                        <th class="py-3.5 px-4">Objek Target</th>
                        <th class="py-3.5 px-4">Detail Nilai</th>
                        <th class="py-3.5 px-4 text-right">IP &amp; Device</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($logs as $log)
                        @php
                            $targetName = '-';
                            $targetType = class_basename($log->auditable_type);
                            if ($log->auditable) {
                                $targetName = $log->auditable->name ?? ($log->auditable->title ?? ('#' . $log->auditable_id));
                            }
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                <div>{{ $log->created_at->format('Y-m-d H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-white text-xs">{{ $log->user->name ?? 'Sistem' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $log->user->email ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider
                                    {{ str_contains($log->action, 'verify') || str_contains($log->action, 'active') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' :
                                       (str_contains($log->action, 'reject') || str_contains($log->action, 'suspend') || str_contains($log->action, 'deactive') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' :
                                       'bg-blue-500/20 text-blue-300 border border-blue-500/30') }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-slate-300 mb-1 inline-block">
                                    {{ $targetType }} #{{ $log->auditable_id }}
                                </span>
                                <div class="font-semibold text-slate-200 truncate max-w-[150px]">{{ $targetName }}</div>
                            </td>
                            <td class="py-4 px-4 max-w-xs text-[11px] text-slate-400">
                                @if($log->new_values)
                                    <pre class="bg-slate-950 p-2 rounded-xl text-[10px] font-mono overflow-x-auto max-h-20 text-slate-300 border border-slate-800/80">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                <div>{{ $log->ip_address ?: '-' }}</div>
                                <div class="text-[10px] text-slate-400 truncate max-w-[120px] ml-auto" title="{{ $log->user_agent }}">{{ $log->user_agent ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada rekam jejak audit yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
