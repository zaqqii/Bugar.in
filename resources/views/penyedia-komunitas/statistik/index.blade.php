@extends('layouts.penyedia-komunitas')

@section('title', 'Statistik - Penyedia Komunitas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                Analisis &amp; Pertumbuhan
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Statistik</h1>
            <p class="text-slate-500 text-sm mt-1">Pantau perkembangan peserta, tren kehadiran, dan performa komunitas.</p>
        </div>
    </div>

    {{-- Placeholder Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-14 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800 mb-2">Halaman ini sedang disiapkan</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto">
            Grafik analitik mendalam mengenai retensi anggota, rating, dan laporan bulanan sedang dalam tahap persiapan.
        </p>
    </div>
</div>
@endsection
