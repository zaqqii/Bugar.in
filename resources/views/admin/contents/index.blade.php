@extends('layouts.admin')

@section('title', 'Kelola Konten Edukasi — Console Ops')

@section('content')
<div class="min-h-screen bg-[#0B1120] text-slate-100 p-6 lg:p-8 space-y-6 font-sans">

    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#111827] via-[#131C31] to-[#0F172A] border border-slate-800/90 p-6 lg:p-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/70 text-xs font-medium text-cyan-400 mb-3">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                Pusat Konten Edukasi
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                Kelola Konten Edukasi
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Buat, ubah, terbitkan atau tarik konten edukasi untuk ditampilkan di portal pengguna.
            </p>
        </div>

        <a href="{{ route('admin.contents.create') }}"
           class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-lg transition inline-flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Konten
        </a>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter --}}
    <div class="rounded-2xl bg-[#111827] border border-slate-800 p-5">
        <form method="GET" action="{{ route('admin.contents.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Cari Judul</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul konten..."
                       class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 placeholder-slate-500 px-3.5 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Jenis</label>
                <select id="type" name="type"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/80 text-sm text-slate-100 px-3.5 py-2.5 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Jenis</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('admin.contents.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Konten --}}
    <div class="rounded-2xl bg-[#111827] border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3.5">Judul &amp; Slug</th>
                        <th class="px-5 py-3.5">Jenis</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Tanggal Terbit</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/70 text-xs">
                    @forelse($contents as $item)
                        <tr class="hover:bg-slate-900/50 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-white text-sm">{{ $item->title }}</div>
                                <div class="text-slate-400 font-mono text-[11px] mt-0.5">/edukasi/{{ $item->slug }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                {{ $types[$item->type] ?? $item->type }}
                            </td>
                            <td class="px-5 py-4">
                                @if($item->status === 'published')
                                    <span class="px-2.5 py-1 rounded-md font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        Terbit
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md font-semibold bg-slate-700/40 text-slate-300 border border-slate-600/40">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                {{ $item->published_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.contents.toggle-publish', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg font-semibold {{ $item->status === 'published' ? 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/30' : 'bg-emerald-600 text-white hover:bg-emerald-500' }} transition cursor-pointer">
                                            {{ $item->status === 'published' ? 'Tarik' : 'Terbitkan' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.contents.edit', $item) }}"
                                       class="px-3 py-1.5 rounded-lg font-semibold bg-blue-600/20 text-blue-300 hover:bg-blue-600/30 border border-blue-500/30 transition">
                                        Ubah
                                    </a>

                                    <form method="POST" action="{{ route('admin.contents.destroy', $item) }}"
                                          onsubmit="return confirm('Hapus konten \'{{ addslashes($item->title) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg font-semibold bg-rose-600/20 text-rose-300 hover:bg-rose-600/30 border border-rose-500/30 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                Belum ada konten edukasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contents->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-800 bg-slate-950/40">
                {{ $contents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection