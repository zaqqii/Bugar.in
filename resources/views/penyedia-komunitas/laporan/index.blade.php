@extends('layouts.penyedia-komunitas')

@section('title', 'Laporan Anggota — Penyedia Komunitas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Moderasi Komunitas
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Anggota</h1>
            <p class="text-slate-500 text-sm mt-1">
                Tinjau aduan pelanggaran dari pengguna terhadap anggota di komunitas <strong class="text-slate-800">{{ $community->name }}</strong>.
            </p>
        </div>

        <div>
            <a href="{{ route('penyedia-komunitas.dashboard') }}"
               class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 transition-all inline-flex items-center gap-2 shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke dashboard</span>
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
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

    {{-- Container AJAX / Alpine.js --}}
    <div x-data="{
             currentStatus: '{{ $status ?? '' }}',
             isLoading: false,
             async fetchReports(url = null) {
                 this.isLoading = true;
                 const targetUrl = new URL(url || '{{ route('penyedia-komunitas.laporan.index') }}', window.location.origin);
                 if (this.currentStatus) {
                     targetUrl.searchParams.set('status', this.currentStatus);
                 } else {
                     targetUrl.searchParams.delete('status');
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
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
            <button type="button"
                    @click="currentStatus = ''; fetchReports()"
                    :class="currentStatus === '' ? 'bg-primary-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Semua ({{ $counts['all'] }})
            </button>
            <button type="button"
                    @click="currentStatus = 'pending'; fetchReports()"
                    :class="currentStatus === 'pending' ? 'bg-amber-500 text-white font-bold shadow-xs' : 'bg-white text-amber-700 hover:text-amber-800 border border-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Belum dicek ({{ $counts['pending'] }})
            </button>
            <button type="button"
                    @click="currentStatus = 'under_review'; fetchReports()"
                    :class="currentStatus === 'under_review' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white text-blue-700 hover:text-blue-800 border border-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Sedang ditinjau ({{ $counts['under_review'] }})
            </button>
            <button type="button"
                    @click="currentStatus = 'resolved'; fetchReports()"
                    :class="currentStatus === 'resolved' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-emerald-700 hover:text-emerald-800 border border-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Disetujui / Sanksi ({{ $counts['resolved'] }})
            </button>
            <button type="button"
                    @click="currentStatus = 'dismissed'; fetchReports()"
                    :class="currentStatus === 'dismissed' ? 'bg-slate-700 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-800 border border-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Ditolak ({{ $counts['dismissed'] }})
            </button>
        </div>

        {{-- Tabel Laporan dengan Loading Overlay --}}
        <div class="relative rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-xs min-h-[160px]">
            {{-- Loading Overlay --}}
            <div x-show="isLoading"
                 x-cloak
                 class="absolute inset-0 bg-white/70 backdrop-blur-2xs rounded-2xl flex items-center justify-center z-10"
                 style="display: none;">
                <div class="flex items-center gap-2 text-xs font-mono text-primary-600">
                    <svg class="animate-spin h-4 w-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memperbarui data laporan...</span>
                </div>
            </div>

            <div x-ref="tableContainer">
                @include('penyedia-komunitas.laporan._table', ['reports' => $reports])
            </div>
        </div>

    </div>

</div>
@endsection
