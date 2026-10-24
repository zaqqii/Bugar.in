@extends('layouts.penyedia-komunitas')

@section('title', 'Akun - Penyedia Komunitas')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                Pengaturan
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Akun</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data profil penanggung jawab komunitas dan pengaturan akun Anda.</p>
        </div>
    </div>

    {{-- Placeholder Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-14 text-center shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800 mb-2">Halaman ini sedang disiapkan</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto">
            Fitur pengubahan profil penanggung jawab, kontak PIC, dan keamanan akun sedang dalam tahap persiapan.
        </p>
    </div>
</div>
@endsection
