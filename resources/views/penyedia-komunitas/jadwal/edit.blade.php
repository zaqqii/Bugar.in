@extends('layouts.penyedia-komunitas')

@section('title', 'Edit Jadwal: ' . $schedule->title . ' - Penyedia Komunitas')

@section('content')
<div class="max-w-4xl mx-auto pb-16">
    {{-- Breadcrumb & Back --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('penyedia-komunitas.jadwal.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Jadwal
        </a>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold tracking-wide uppercase mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            EDIT JADWAL &bull; {{ strtoupper($community->name) }}
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight line-clamp-2 break-words">
            Edit Jadwal: {{ $schedule->title }}
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            Perbarui detail jam, hari, lokasi latihan, atau kapasitas peserta jadwal rutin ini.
        </p>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('penyedia-komunitas.jadwal.update', $schedule) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('penyedia-komunitas.jadwal._form', [
            'schedule' => $schedule,
            'community' => $community
        ])

        {{-- Form Actions Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center justify-between gap-3">
            <a href="{{ route('penyedia-komunitas.jadwal.index') }}"
               class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white font-bold text-sm shadow-lg shadow-blue-500/20 transition-all duration-200 hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
@endsection

