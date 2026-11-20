@extends('layouts.user')

@section('title', 'Lapor Komunitas — Bugar.in')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    {{-- Navigasi ringkas --}}
    <div class="flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
        <span>/</span>
        <a href="{{ route('user.communities.show', $community->slug ?? $community->id) }}" class="hover:text-blue-600 transition-colors truncate max-w-[150px]">
            {{ $community->name }}
        </a>
        <span>/</span>
        <span class="text-rose-600 font-semibold">Lapor komunitas</span>
    </div>

    {{-- Header ringkas --}}
    <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                    Laporkan komunitas
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Aduan pelanggaran terhadap komunitas olahraga.
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0112 3.464z" />
                </svg>
            </div>
        </div>

        {{-- Entitas Terlapor Card --}}
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($community->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-slate-800 truncate">{{ $community->name }}</div>
                    <div class="text-[10px] text-slate-400 capitalize">{{ $community->sport_category ?? 'Olahraga' }}</div>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                Komunitas
            </span>
        </div>
    </div>

    {{-- Cek jika ada laporan aktif yang belum selesai --}}
    @if($existingReport)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2">
            <div class="flex items-center gap-2 font-semibold text-amber-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Laporan sedang diproses admin</span>
            </div>
            <p class="text-slate-600">
                Anda sudah mengirimkan laporan pada <strong>{{ $existingReport->created_at->translatedFormat('d M Y') }}</strong>.
            </p>
            <a href="{{ route('user.reports.index') }}" class="inline-flex items-center gap-1 font-semibold text-amber-900 underline text-[11px]">
                <span>Lihat riwayat laporan</span>
                <span>&rarr;</span>
            </a>
        </div>
    @else
        {{-- Form Pelaporan --}}
        <form method="POST" action="{{ route('user.reports.community.store', $community->slug ?? $community->id) }}" enctype="multipart/form-data"
              x-data="{
                  descText: '{{ addslashes(old('description', '')) }}',
                  submitAttempted: false,
                  get isDescInvalid() {
                      return this.descText.trim().length < 15;
                  },
                  validateAndSubmit(e) {
                      this.submitAttempted = true;
                      if (this.isDescInvalid) {
                          e.preventDefault();
                          const el = document.getElementById('description');
                          if (el) el.focus();
                          return false;
                      }
                      return confirm('Kirim laporan ini ke tim admin?');
                  }
              }"
              @submit="if (!validateAndSubmit($event)) $event.preventDefault()"
              class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
            @csrf

            {{-- Alasan Pelaporan --}}
            <div class="space-y-1">
                <label for="reason" class="block text-xs font-semibold text-slate-700">
                    Alasan pelaporan <span class="text-rose-500">*</span>
                </label>
                <select id="reason" name="reason" required
                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors">
                    <option value="" disabled {{ old('reason') ? '' : 'selected' }}>Pilih kategori pelanggaran...</option>
                    @foreach($reasons as $r)
                        <option value="{{ $r }}" {{ old('reason') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                @error('reason')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi Kronologi --}}
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <label for="description" class="font-semibold text-slate-700">
                        Deskripsi kejadian <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] font-mono" :class="descText.trim().length >= 15 ? 'text-emerald-600' : (descText.trim().length > 0 ? 'text-rose-500' : 'text-slate-400')">
                        <span x-text="descText.trim().length"></span>/15 karakter
                    </span>
                </div>
                <textarea id="description" name="description" rows="4" required
                          x-model="descText"
                          placeholder="Jelaskan kronologi kejadian secara ringkas..."
                          :class="(submitAttempted && isDescInvalid) || {{ $errors->has('description') ? 'true' : 'false' }}
                              ? 'border-rose-500 bg-rose-50/30 text-rose-900 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                              : 'border-slate-200 bg-slate-50 text-slate-800 focus:border-blue-600 focus:bg-white'"
                          class="w-full px-3.5 py-2 rounded-xl border text-xs focus:outline-hidden transition-colors resize-none">{{ old('description') }}</textarea>

                <template x-if="submitAttempted && isDescInvalid">
                    <p class="text-xs text-rose-500 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>Deskripsi wajib minimal 15 karakter (saat ini <span x-text="descText.trim().length"></span> karakter).</span>
                    </p>
                </template>

                @error('description')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bukti Pendukung (Link/Tautan) --}}
            <div class="space-y-1">
                <label for="evidence_url" class="block text-xs font-semibold text-slate-700">
                    Tautan bukti pendukung (opsional)
                </label>
                <input type="url" id="evidence_url" name="evidence_url"
                       placeholder="https://..."
                       value="{{ old('evidence_url') }}"
                       class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors">
                @error('evidence_url')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Unggah Foto Bukti Privat --}}
            <div class="space-y-1" x-data="{
                files: [],
                errorMessage: '',
                allowedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'],
                allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
                handleFileSelect(event) {
                    this.errorMessage = '';
                    const selectedFiles = Array.from(event.target.files);

                    if (selectedFiles.length > 5) {
                        this.errorMessage = 'Maksimal 5 foto bukti yang dapat dipilih. Silakan kurangi jumlah foto.';
                        event.target.value = '';
                        this.files = [];
                        return;
                    }

                    const invalidFiles = selectedFiles.filter(f => {
                        const ext = f.name.split('.').pop().toLowerCase();
                        return !this.allowedTypes.includes(f.type) && !this.allowedExtensions.includes(ext);
                    });

                    if (invalidFiles.length > 0) {
                        this.errorMessage = 'Format file tidak didukung (' + invalidFiles.map(f => f.name).join(', ') + '). Hanya diperbolehkan JPG, PNG, dan WEBP.';
                        event.target.value = '';
                        this.files = [];
                        return;
                    }

                    const oversizedFiles = selectedFiles.filter(f => f.size > 2048 * 1024);
                    if (oversizedFiles.length > 0) {
                        this.errorMessage = 'Ukuran file terlalu besar (' + oversizedFiles.map(f => f.name).join(', ') + '). Maksimal 2MB per foto.';
                        event.target.value = '';
                        this.files = [];
                        return;
                    }

                    this.files = selectedFiles.map(f => ({
                        name: f.name,
                        size: (f.size / 1024).toFixed(1) + ' KB'
                    }));
                }
            }">
                <div class="flex items-center justify-between text-xs">
                    <label for="evidence_images" class="font-semibold text-slate-700">
                        Foto bukti (opsional, maks. 5)
                    </label>
                    <span class="text-[11px] text-slate-400">JPG, PNG, WEBP &le; 2MB</span>
                </div>
                <input type="file" id="evidence_images" name="evidence_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                       @change="handleFileSelect"
                       class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs focus:outline-hidden focus:border-blue-600 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                {{-- Alert Error Validasi Frontend --}}
                <template x-if="errorMessage">
                    <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>

                {{-- Daftar Berkas Terpilih --}}
                <template x-if="files.length > 0">
                    <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-200/50 space-y-1.5">
                        <span class="text-[11px] font-semibold text-blue-900 block">Foto dipilih (<span x-text="files.length"></span>/5):</span>
                        <ul class="space-y-1 text-xs">
                            <template x-for="(file, idx) in files" :key="idx">
                                <li class="flex items-center justify-between gap-2 bg-white px-2.5 py-1 rounded-lg border border-blue-100">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                        </svg>
                                        <span class="truncate text-slate-700" x-text="file.name"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono flex-shrink-0" x-text="file.size"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>

                @error('evidence_images')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
                @error('evidence_images.*')
                    <p class="text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-2 flex items-center justify-end gap-2.5">
                <a href="{{ route('user.communities.show', $community->slug ?? $community->id) }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                    Kirim laporan
                </button>
            </div>
        </form>
    @endif

</div>
@endsection
