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

    {{-- Container AJAX / SPA Filter Laporan --}}
    <div x-data="{
             currentStatus: '{{ $status ?? '' }}',
             currentType: '{{ $type ?? '' }}',
             isLoading: false,
             async fetchReports(url = null) {
                 this.isLoading = true;
                 const targetUrl = new URL(url || '{{ route('admin.reports.index') }}', window.location.origin);
                 if (this.currentStatus) {
                     targetUrl.searchParams.set('status', this.currentStatus);
                 } else {
                     targetUrl.searchParams.delete('status');
                 }
                 if (this.currentType) {
                     targetUrl.searchParams.set('type', this.currentType);
                 } else {
                     targetUrl.searchParams.delete('type');
                 }
                 try {
                     const response = await fetch(targetUrl, {
                         headers: {
                             'X-Requested-With': 'XMLHttpRequest',
                             'Accept': 'text/html'
                         }
                     });
                     if (response.ok) {
                         const html = await response.text();
                         this.$refs.tableContainer.innerHTML = html;
                         window.history.replaceState({}, '', targetUrl);
                     }
                 } catch (e) {
                     console.error('Gagal mengambil data laporan:', e);
                 } finally {
                     this.isLoading = false;
                 }
             }
         }"
         class="space-y-4">

        {{-- Filter toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-3">
            {{-- Filter status tabs --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button"
                        @click="currentStatus = ''; fetchReports()"
                        :class="currentStatus === '' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                    Semua ({{ $counts['all'] }})
                </button>
                <button type="button"
                        @click="currentStatus = 'pending'; fetchReports()"
                        :class="currentStatus === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-amber-400/80 hover:text-amber-300 border border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                    Belum dicek ({{ $counts['pending'] }})
                </button>
                <button type="button"
                        @click="currentStatus = 'under_review'; fetchReports()"
                        :class="currentStatus === 'under_review' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-900 text-blue-400/80 hover:text-blue-300 border border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                    Sedang ditinjau ({{ $counts['under_review'] }})
                </button>
                <button type="button"
                        @click="currentStatus = 'resolved'; fetchReports()"
                        :class="currentStatus === 'resolved' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-900 text-emerald-400/80 hover:text-emerald-300 border border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                    Selesai ({{ $counts['resolved'] }})
                </button>
                <button type="button"
                        @click="currentStatus = 'dismissed'; fetchReports()"
                        :class="currentStatus === 'dismissed' ? 'bg-slate-700 text-white font-bold' : 'bg-slate-900 text-slate-400 hover:text-slate-300 border border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                    Ditolak ({{ $counts['dismissed'] }})
                </button>
            </div>

            {{-- Filter tipe target --}}
            <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800 text-xs flex-shrink-0">
                <button type="button"
                        @click="currentType = ''; fetchReports()"
                        :class="currentType === '' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1 rounded-lg font-semibold transition-all cursor-pointer">
                    Semua target
                </button>
                <button type="button"
                        @click="currentType = 'community'; fetchReports()"
                        :class="currentType === 'community' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1 rounded-lg font-semibold transition-all cursor-pointer">
                    Komunitas
                </button>
                <button type="button"
                        @click="currentType = 'user'; fetchReports()"
                        :class="currentType === 'user' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1 rounded-lg font-semibold transition-all cursor-pointer">
                    Pengguna
                </button>
            </div>
        </div>

        {{-- Tabel laporan dengan Indikator Loading --}}
        <div class="relative rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm min-h-[160px]">
            {{-- Loading Overlay --}}
            <div x-show="isLoading"
                 x-cloak
                 class="absolute inset-0 bg-slate-900/70 backdrop-blur-2xs rounded-2xl flex items-center justify-center z-10"
                 style="display: none;">
                <div class="flex items-center gap-2 text-xs font-mono text-cyan-400">
                    <svg class="animate-spin h-4 w-4 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memperbarui data laporan...</span>
                </div>
            </div>

            <div x-ref="tableContainer">
                @include('admin.reports._table', ['reports' => $reports])
            </div>
        </div>

    </div>

</div>
@endsection
