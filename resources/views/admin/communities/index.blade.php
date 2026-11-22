@extends('layouts.admin')

@section('title', 'Kelola Klub & Komunitas — Console Ops')

@section('content')
<div class="min-h-screen bg-[#0B1120] text-slate-100 p-6 lg:p-8 space-y-6 font-sans">

    {{-- Flash Alert --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm">
            <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Hero Header Card --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#111827] via-[#131C31] to-[#0F172A] border border-slate-800/90 p-6 lg:p-7 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/70 text-xs font-medium text-cyan-400 mb-3">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Direktori & Tata Kelola Komunitas
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                Kelola Klub & Komunitas
            </h1>
            <p class="text-sm text-slate-400 mt-1 max-w-2xl">
                Pantau seluruh komunitas olahraga terdaftar, kelola status keaktifan operasional, serta audit kelayakan verifikasi penyelenggara.
            </p>
        </div>

        {{-- Ringkasan Angka Cepat --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0">
            <div class="px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800">
                <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Total</div>
                <div class="text-xl font-bold text-white mt-0.5">{{ $stats['total'] }}</div>
            </div>
            <div class="px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800">
                <div class="text-[11px] uppercase tracking-wider text-emerald-400 font-semibold">Verified</div>
                <div class="text-xl font-bold text-emerald-400 mt-0.5">{{ $stats['verified'] }}</div>
            </div>
            <div class="px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800">
                <div class="text-[11px] uppercase tracking-wider text-rose-400 font-semibold">Suspended</div>
                <div class="text-xl font-bold text-rose-400 mt-0.5">{{ $stats['suspended'] }}</div>
            </div>
            <div class="px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800">
                <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Nonaktif</div>
                <div class="text-xl font-bold text-slate-300 mt-0.5">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    {{-- BAR PENCARIAN & FILTER (4 Kolom Ukuran Sama Rata) --}}
    <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
        <form id="filterForm" method="GET" action="{{ route('admin.communities.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-5 items-end">
            
            {{-- 1. Cari Nama Komunitas (Lebar: 3 Kolom) --}}
            <div class="lg:col-span-3">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Cari Nama Komunitas
                </label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Ketik nama & tekan Enter..."
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 placeholder-slate-500 pl-10 pr-4 py-2.5 focus:outline-none focus:border-blue-500">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- 2. Filter Status Verifikasi (Lebar: 3 Kolom) --}}
            <div class="lg:col-span-3">
                <label for="verification_status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Status Verifikasi
                </label>
                <select id="verification_status" name="verification_status"
                    class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 px-3.5 py-2.5 focus:outline-none focus:border-blue-500 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="verified" @selected(request('verification_status') === 'verified')>Verified (Terverifikasi)</option>
                    <option value="pending" @selected(request('verification_status') === 'pending')>Pending (Menunggu)</option>
                    <option value="suspended" @selected(request('verification_status') === 'suspended')>Suspended (Dicabut)</option>
                    <option value="rejected" @selected(request('verification_status') === 'rejected')>Rejected (Ditolak)</option>
                </select>
            </div>

            {{-- 3. Filter Status Operasional (Lebar: 3 Kolom) --}}
            <div class="lg:col-span-3">
                <label for="is_active" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Status Operasional
                </label>
                <select id="is_active" name="is_active"
                    class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 px-3.5 py-2.5 focus:outline-none focus:border-blue-500 cursor-pointer">
                    <option value="">Semua Status Operasional</option>
                    <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                    <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
                </select>
            </div>

            {{-- 4. Filter Cabang Olahraga (Lebar: 3 Kolom) --}}
            <div class="lg:col-span-3">
                <label for="sport_category" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Cabang Olahraga
                </label>
                <select id="sport_category" name="sport_category"
                    class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 px-3.5 py-2.5 focus:outline-none focus:border-blue-500 cursor-pointer">
                    <option value="">Semua Cabang Olahraga</option>
                    @foreach($sportCategories as $sport)
                        <option value="{{ $sport }}" @selected(request('sport_category') === $sport)>
                            {{ ucfirst($sport) }}
                        </option>
                    @endforeach
                </select>
            </div>

        </form>
    </div>

    {{-- KONTAINER TABEL DENGAN SPINNER PUTIH --}}
    <div class="relative rounded-2xl bg-[#111827] border border-slate-800 overflow-hidden shadow-xl">
        
        {{-- Overlay Spinner Putih --}}
        <div id="loadingOverlay" class="absolute inset-0 bg-[#0B1120]/75 backdrop-blur-xs flex items-center justify-center z-20 hidden">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 shadow-2xl text-white text-xs font-semibold">
                <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyaring data komunitas...</span>
            </div>
        </div>

        {{-- Isi Tabel --}}
        <div id="table-container">
            @include('admin.communities.partials.table', ['communities' => $communities])
        </div>
    </div>

</div>

{{-- SCRIPT AJAX AMAN TANPA PENUMPUKAN URL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    const tableContainer = document.getElementById('table-container');
    const loadingOverlay = document.getElementById('loadingOverlay');

    async function fetchTableData(url) {
        loadingOverlay.classList.remove('hidden');
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            tableContainer.innerHTML = html;
            window.history.pushState({}, '', url);
        } catch (error) {
            console.error('Gagal memuat data:', error);
            window.location.href = url;
        } finally {
            loadingOverlay.classList.add('hidden');
        }
    }

    // Filter Akumulatif saat dropdown diubah
    form.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function () {
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    params.append(key, value);
                }
            }

            const queryString = params.toString() ? `?${params.toString()}` : '';
            fetchTableData(`${form.action}${queryString}`);
        });
    });

    // Pencarian dengan Enter
    const searchInput = form.querySelector('#search');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                const queryString = params.toString() ? `?${params.toString()}` : '';
                fetchTableData(`${form.action}${queryString}`);
            }
        });
    }

    // Paginasi via AJAX
    tableContainer.addEventListener('click', function (e) {
        const link = e.target.closest('.pagination a, nav a');
        if (link && link.href) {
            e.preventDefault();
            fetchTableData(link.href);
        }
    });
});
</script>
@endsection