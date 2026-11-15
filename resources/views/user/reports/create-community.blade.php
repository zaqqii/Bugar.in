@extends('layouts.user')

@section('title', 'Lapor Komunitas — Bugar.in')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
        <span>/</span>
        <a href="{{ route('user.communities.show', $community->slug ?? $community->id) }}" class="hover:text-blue-600 transition-colors truncate max-w-[150px]">
            {{ $community->name }}
        </a>
        <span>/</span>
        <span class="text-rose-600 font-semibold">Lapor Komunitas</span>
    </div>

    {{-- Header Banner --}}
    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-xl flex-shrink-0">
                🛡️
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Laporkan Komunitas
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                    Bantu kami menjaga ekosistem olahraga Bugar.in tetap aman, suportif, dan terpercaya. Identitas Anda dirahasiakan dari pihak terlapor.
                </p>
            </div>
        </div>

        {{-- Entitas Terlapor Card --}}
        <div class="mt-5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($community->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Komunitas yang Dilaporkan</span>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 truncate">{{ $community->name }}</h3>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 capitalize flex-shrink-0">
                {{ $community->sport_category ?? 'Olahraga' }}
            </span>
        </div>
    </div>

    {{-- Cek jika ada laporan aktif yang belum selesai --}}
    @if($existingReport)
        <div class="p-5 rounded-3xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm space-y-2">
            <div class="flex items-center gap-2 font-bold text-amber-800">
                <span class="text-base">⏳</span>
                <span>Laporan Sedang Diproses Admin</span>
            </div>
            <p class="leading-relaxed">
                Anda sudah mengirimkan laporan terhadap komunitas ini pada <strong>{{ $existingReport->created_at->translatedFormat('d F Y, H:i') }} WIB</strong> dengan status <span class="capitalize font-semibold underline">{{ str_replace('_', ' ', $existingReport->status) }}</span>.
            </p>
            <div class="pt-2">
                <a href="{{ route('user.reports.index') }}" class="inline-flex items-center gap-1.5 font-bold text-amber-900 hover:text-amber-950 underline">
                    <span>Lihat riwayat laporan Anda</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    @else
        {{-- Form Pelaporan --}}
        <form method="POST" action="{{ route('user.reports.community.store', $community->slug ?? $community->id) }}" class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            @csrf

            {{-- Alasan Pelaporan --}}
            <div class="space-y-1.5">
                <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Alasan Pelaporan <span class="text-rose-500">*</span>
                </label>
                <select id="reason" name="reason" required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors">
                    <option value="" disabled {{ old('reason') ? '' : 'selected' }}>Pilih kategori pelanggaran...</option>
                    @foreach($reasons as $r)
                        <option value="{{ $r }}" {{ old('reason') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                @error('reason')
                    <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi Kronologi --}}
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Deskripsi / Kronologi Kejadian <span class="text-rose-500">*</span>
                </label>
                <textarea id="description" name="description" rows="5" required
                          placeholder="Ceritakan kejadian secara detail: waktu, lokasi, pihak terkait, dan tindakan yang merugikan/melanggar aturan..."
                          class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors resize-none">{{ old('description') }}</textarea>
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Minimal 15 karakter</span>
                </div>
                @error('description')
                    <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bukti Pendukung (Link/Tautan) --}}
            <div class="space-y-1.5">
                <label for="evidence_url" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Tautan Bukti Pendukung (Opsional)
                </label>
                <input type="url" id="evidence_url" name="evidence_url"
                       placeholder="https://drive.google.com/... atau https://prnt.sc/..."
                       value="{{ old('evidence_url') }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors">
                <p class="text-[11px] text-slate-400">
                    Sertakan tautan screenshot, bukti chat, atau dokumen pendukung di cloud storage jika ada.
                </p>
                @error('evidence_url')
                    <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Disclaimer Keamanan --}}
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/60 text-xs text-blue-900 leading-relaxed flex items-start gap-2.5">
                <span class="text-base leading-none">🔒</span>
                <div>
                    Laporan palsu atau bertujuan mencemarkan nama baik tanpa bukti dapat dikenakan sanksi pembatasan akun. Pastikan informasi yang Anda berikan adalah benar.
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('user.communities.show', $community->slug ?? $community->id) }}"
                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-bold text-center transition-colors">
                    Batal
                </a>
                <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin mengirim laporan ini ke Tim Admin?')"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold shadow-sm shadow-rose-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Kirim Laporan</span>
                </button>
            </div>
        </form>
    @endif

</div>
@endsection
