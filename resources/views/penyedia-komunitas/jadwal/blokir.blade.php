@extends('layouts.penyedia-komunitas')

@section('title', 'Daftar Anggota Terblokir - Penyedia Komunitas')

@section('content')
<div class="max-w-4xl mx-auto pb-16 space-y-6">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('penyedia-komunitas.jadwal.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Jadwal
        </a>
    </div>

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                KEAMANAN KOMUNITAS &bull; {{ strtoupper($community->name) }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Daftar Anggota Terblokir
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pengguna di daftar ini tidak dapat bergabung dengan seluruh jadwal sesi yang diselenggarakan oleh {{ $community->name }}.
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900">Anggota Diblokir</h2>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                Total: {{ $blocks->total() }} Pengguna
            </span>
        </div>

        @if($blocks->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 font-bold text-xl">
                    ✓
                </div>
                <h4 class="text-sm font-bold text-slate-800">Tidak Ada Anggota yang Diblokir</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto mt-1">
                    Semua pengguna memiliki hak normal untuk bergabung ke jadwal sesi komunitas Anda.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                            <th class="py-3 px-5">Pengguna</th>
                            <th class="py-3 px-5">Alasan Pemblokiran</th>
                            <th class="py-3 px-5">Diblokir Oleh</th>
                            <th class="py-3 px-5">Tanggal Diblokir</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($blocks as $block)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($block->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-bold text-slate-900">
                                                {{ $block->user->name ?? 'Pengguna #' . $block->user_id }}
                                            </span>
                                            <span class="block text-[11px] text-slate-400">
                                                {{ $block->user->email ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600">
                                    {{ $block->reason ?? 'Tidak ada alasan dicantumkan' }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-500">
                                    {{ $block->blocker->name ?? 'Pengelola' }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-500">
                                    {{ $block->created_at ? $block->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <form method="POST" action="{{ route('penyedia-komunitas.jadwal.unblock', $block) }}" onsubmit="return confirm('Buka blokir untuk pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition shadow-2xs">
                                            Buka Blokir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($blocks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $blocks->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

