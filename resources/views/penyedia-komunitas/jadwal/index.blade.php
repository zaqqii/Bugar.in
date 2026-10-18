@extends('layouts.penyedia-komunitas')

@section('title', 'Jadwal - Penyedia Komunitas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                Agenda Rutin
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Jadwal</h1>
            <p class="text-slate-500 text-sm mt-1">Atur dan kelola sesi latihan rutin mingguan komunitas Anda.</p>
        </div>
    </div>

    {{-- Placeholder Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-14 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800 mb-2">Halaman ini sedang disiapkan</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto">
            Fitur pembuatan jadwal mingguan, kuota sesi latihan, dan absensi rutin sedang dalam tahap pengembangan.
        </p>
    </div>
</div>
@endsection
