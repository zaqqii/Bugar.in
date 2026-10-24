@extends('layouts.penyedia-komunitas')

@section('title', 'Komunitas - Penyedia Komunitas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                Manajemen Komunitas
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Komunitas</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola profil, informasi publik, dan keanggotaan komunitas Anda.</p>
        </div>
    </div>

    {{-- Placeholder Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-14 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800 mb-2">Halaman ini sedang disiapkan</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto">
            Fitur pengelolaan profil komunitas dan daftar anggota sedang dalam tahap persiapan dan akan segera tersedia.
        </p>
    </div>
</div>
@endsection
