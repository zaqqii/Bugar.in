@php
    $todayDate = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
    $scheduleDate = old('date', $schedule->date ? $schedule->date->format('Y-m-d') : $todayDate);

    $initialForm = [
        'title' => old('title', $schedule->title ?? ''),
        'level' => old('level', $schedule->level ?? 'all_levels'),
        'description' => old('description', $schedule->description ?? ''),
        'location' => old('location', $schedule->location ?? ''),
        'maps_url' => old('maps_url', $schedule->maps_url ?? ''),
        'date' => $scheduleDate,
        'start_time' => old('start_time', isset($schedule->start_time) ? substr($schedule->start_time, 0, 5) : '07:00'),
        'end_time' => old('end_time', isset($schedule->end_time) ? substr($schedule->end_time, 0, 5) : '09:00'),
        'capacity_type' => old('capacity_type', ($schedule->exists && $schedule->capacity === null) ? 'unlimited' : (($schedule->exists && $schedule->capacity !== null) ? 'limited' : 'unlimited')),
        'capacity' => old('capacity', $schedule->capacity ?? ''),
        'repeat_weekly_monthly' => (bool) old('repeat_weekly_monthly', false),
        'is_active' => (bool) old('is_active', $schedule->exists ? $schedule->is_active : true),
        'streak_enabled' => (bool) old('streak_enabled', $schedule->exists ? $schedule->streak_enabled : false),
    ];

    $isVerified = $community->isVerified();
    $activeParticipants = $schedule->exists ? $schedule->activeParticipantsCount() : 0;
    $isTimeLocked = $schedule->exists && $activeParticipants > 0;
@endphp

<div x-data="{
    form: @json($initialForm),
    capacityType: @json($initialForm['capacity_type']),
    isVerified: @json($isVerified),
    isTimeLocked: @json($isTimeLocked)
}" class="space-y-6">

    {{-- Kategori Cabor (Otomatis dari Komunitas) --}}
    <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0V5.625c0-.621-.504-1.125-1.125-1.125H9.375c-.621 0-1.125.504-1.125 1.125v8.625" />
                </svg>
            </div>
            <div>
                <span class="block text-[11px] font-bold text-blue-600 uppercase tracking-wider">Kategori Olahraga</span>
                <span class="text-sm font-extrabold text-slate-800">{{ $community->sport_category ?? 'Umum' }}</span>
                <span class="text-xs text-slate-500 ml-1.5 font-normal">&bull; Mengikuti profil komunitas {{ $community->name }}</span>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold shrink-0">
            Terhubung
        </span>
    </div>

    {{-- Alert jika waktu terkunci --}}
    @if($isTimeLocked)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
            <span class="text-lg">⚠️</span>
            <div>
                <strong class="font-bold">Tanggal & Waktu Terkunci:</strong>
                Sesi jadwal ini sudah memiliki <strong>{{ $activeParticipants }} peserta aktif</strong> terdaftar. Tanggal dan jam tidak dapat diubah demi kenyamanan peserta. Jika ingin mengganti jadwal pelaksanaan, silakan batalkan sesi ini dan buat jadwal baru.
            </div>
        </div>
    @endif

    {{-- Field 1: Judul Jadwal & Tingkat Level --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Judul Jadwal <span class="text-rose-500">*</span>
                </label>
                <span class="text-[11px] font-semibold"
                      :class="form.title.length > 70 ? 'text-amber-600' : 'text-slate-400'"
                      x-text="`${form.title.length} / 80`"></span>
            </div>
            <input type="text"
                   id="title"
                   name="title"
                   x-model="form.title"
                   maxlength="80"
                   required
                   placeholder="Contoh: Latihan Rutin Basket Sore, Badminton Fun Match Rabu"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('title') border-rose-400 bg-rose-50/30 @enderror">
            @error('title')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400 mt-1">Nama jadwal yang ringkas dan jelas, maksimal 80 karakter.</p>
        </div>

        {{-- Level / Tingkat Kemahiran --}}
        <div>
            <label for="level" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                Tingkat Kemahiran (Level) <span class="text-rose-500">*</span>
            </label>
            <select id="level"
                    name="level"
                    x-model="form.level"
                    required
                    class="w-full sm:w-72 px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('level') border-rose-400 bg-rose-50/30 @enderror">
                <option value="all_levels">Semua level (Bebas / Terbuka)</option>
                <option value="beginner">Pemula (Beginner)</option>
                <option value="intermediate">Menengah (Intermediate)</option>
                <option value="advanced">Mahir (Advanced)</option>
            </select>
            @error('level')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400 mt-1">Membantu calon peserta mengetahui kecocokan level sesi ini.</p>
        </div>

        {{-- Field: Deskripsi --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Deskripsi / Catatan Tambahan (Opsional)
                </label>
                <span class="text-[11px] font-semibold"
                      :class="form.description.length > 450 ? 'text-amber-600' : 'text-slate-400'"
                      x-text="`${form.description.length} / 500`"></span>
            </div>
            <textarea id="description"
                      name="description"
                      x-model="form.description"
                      rows="3"
                      maxlength="500"
                      placeholder="Informasi mengenai perlengkapan yang wajib dibawa, instruktur latihan, atau aturan berpakaian..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('description') border-rose-400 bg-rose-50/30 @enderror"></textarea>
            @error('description')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400 mt-1">Maksimal 500 karakter.</p>
        </div>
    </div>

    {{-- Field 2: Tanggal & Waktu Pelaksanaan --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Tanggal &amp; Waktu Pelaksanaan</h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Input Tanggal --}}
            <div>
                <label for="date" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                    Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                </label>
                @if($isTimeLocked)
                    <input type="date"
                           id="date_display"
                           value="{{ $scheduleDate }}"
                           disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                    <input type="hidden" name="date" value="{{ $scheduleDate }}">
                @else
                    <input type="date"
                           id="date"
                           name="date"
                           min="{{ $todayDate }}"
                           x-model="form.date"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('date') border-rose-400 bg-rose-50/30 @enderror">
                @endif
                @error('date')
                    <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jam Mulai --}}
            <div>
                <label for="start_time" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                    Jam Mulai (WIB) <span class="text-rose-500">*</span>
                </label>
                @if($isTimeLocked)
                    <input type="time"
                           id="start_time_display"
                           value="{{ substr($schedule->start_time, 0, 5) }}"
                           disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                    <input type="hidden" name="start_time" value="{{ substr($schedule->start_time, 0, 5) }}">
                @else
                    <input type="time"
                           id="start_time"
                           name="start_time"
                           x-model="form.start_time"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('start_time') border-rose-400 bg-rose-50/30 @enderror">
                @endif
                @error('start_time')
                    <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jam Selesai --}}
            <div>
                <label for="end_time" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                    Jam Selesai (WIB) <span class="text-rose-500">*</span>
                </label>
                @if($isTimeLocked)
                    <input type="time"
                           id="end_time_display"
                           value="{{ substr($schedule->end_time, 0, 5) }}"
                           disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                    <input type="hidden" name="end_time" value="{{ substr($schedule->end_time, 0, 5) }}">
                @else
                    <input type="time"
                           id="end_time"
                           name="end_time"
                           x-model="form.end_time"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('end_time') border-rose-400 bg-rose-50/30 @enderror">
                @endif
                @error('end_time')
                    <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Checkbox Ulangi Mingguan Sampai Akhir Bulan (Hanya Form Create) --}}
        @if(!$schedule->exists)
            <div class="pt-3 border-t border-slate-100">
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-blue-100 bg-blue-50/50 hover:bg-blue-50 cursor-pointer transition">
                    <input type="checkbox"
                           name="repeat_weekly_monthly"
                           value="1"
                           x-model="form.repeat_weekly_monthly"
                           class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">
                            🔁 Ulangi tiap minggu sampai akhir bulan ini
                        </span>
                        <span class="text-[11px] text-slate-600 block mt-0.5">
                            Jika dicentang, sistem otomatis membuat satu sesi per minggu pada hari yang sama mulai dari tanggal yang dipilih hingga akhir bulan berjalan (berbagi ID Seri).
                        </span>
                    </div>
                </label>
            </div>
        @elseif($schedule->series_id)
            <div class="pt-2 text-xs text-blue-700 font-semibold flex items-center gap-1.5">
                <span>🔗</span> Sesi ini merupakan bagian dari jadwal berseri mingguan.
            </div>
        @endif
    </div>

    {{-- Field 3: Lokasi & Maps --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Lokasi Pelaksanaan</h3>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Nama Tempat / Lapangan <span class="text-rose-500">*</span>
                </label>
                <span class="text-[11px] font-semibold"
                      :class="form.location.length > 100 ? 'text-amber-600' : 'text-slate-400'"
                      x-text="`${form.location.length} / 120`"></span>
            </div>
            <input type="text"
                   id="location"
                   name="location"
                   x-model="form.location"
                   maxlength="120"
                   required
                   placeholder="Contoh: GOR Pertiwi Lapangan 2, Lapangan Rampal Malang"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('location') border-rose-400 bg-rose-50/30 @enderror">
            @error('location')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="maps_url" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                Tautan Google Maps (Opsional)
            </label>
            <input type="url"
                   id="maps_url"
                   name="maps_url"
                   x-model="form.maps_url"
                   maxlength="255"
                   placeholder="https://maps.google.com/..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('maps_url') border-rose-400 bg-rose-50/30 @enderror">
            @error('maps_url')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400 mt-1">Membantu peserta menemukan titik lokasi akurat lewat Google Maps.</p>
        </div>
    </div>

    {{-- Field 4: Kapasitas Peserta --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Kapasitas Slot Peserta</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                   :class="capacityType === 'unlimited' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200 hover:bg-slate-50'">
                <input type="radio"
                       name="capacity_type"
                       value="unlimited"
                       x-model="capacityType"
                       @change="capacityType = 'unlimited'"
                       class="text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="block text-xs font-bold text-slate-800">Tanpa Batas (Bebas)</span>
                    <span class="text-[11px] text-slate-500">Bebas diikuti seluruh anggota tanpa kuota maksimal</span>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                   :class="capacityType === 'limited' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200 hover:bg-slate-50'">
                <input type="radio"
                       name="capacity_type"
                       value="limited"
                       x-model="capacityType"
                       @change="capacityType = 'limited'"
                       class="text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="block text-xs font-bold text-slate-800">Batasi Kuota</span>
                    <span class="text-[11px] text-slate-500">Tentukan batas kuota maksimal peserta yang dapat bergabung</span>
                </div>
            </label>
        </div>

        {{-- Input Jumlah Kuota — selalu ada di DOM, hanya visibility yang berubah --}}
        <div x-show="capacityType === 'limited'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             style="display: none;"
             class="pt-3 border-t border-slate-100">
            <label for="capacity" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                Jumlah Slot Peserta <span class="text-rose-500">*</span>
            </label>
            <div class="flex items-center gap-3 max-w-xs">
                {{-- Tombol kurang --}}
                <button type="button"
                        @click="if(parseInt(form.capacity) > {{ max(1, $activeParticipants) }}) form.capacity = parseInt(form.capacity) - 1"
                        class="w-9 h-9 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-lg flex items-center justify-center transition shrink-0 select-none">
                    −
                </button>
                <input type="number"
                       id="capacity"
                       name="capacity"
                       x-model="form.capacity"
                       :required="capacityType === 'limited'"
                       min="{{ max(1, $activeParticipants) }}"
                       placeholder="{{ max(1, $activeParticipants) > 1 ? $activeParticipants : '15' }}"
                       class="w-full text-center px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('capacity') border-rose-400 bg-rose-50/30 @enderror">
                {{-- Tombol tambah --}}
                <button type="button"
                        @click="form.capacity = (parseInt(form.capacity) || 0) + 1"
                        class="w-9 h-9 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-lg flex items-center justify-center transition shrink-0 select-none">
                    +
                </button>
            </div>
            @if($activeParticipants > 0)
                <p class="text-xs text-amber-700 font-medium mt-1.5">
                    Saat ini sudah ada <strong>{{ $activeParticipants }} peserta aktif</strong> terdaftar. Kuota tidak boleh disetel di bawah {{ $activeParticipants }}.
                </p>
            @endif
            @error('capacity')
                <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Field 5: Pengaturan Tambahan (Streak & Status Aktif) --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-5">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Pengaturan Tambahan</h3>

        {{-- Toggle Streak --}}
        <div class="p-4 rounded-xl border transition {{ $isVerified ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-slate-50/70' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <span class="text-amber-500">🔥</span> Fitur Streak Olahraga
                        </span>
                        @if($isVerified)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                Komunitas Terverifikasi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold">
                                Perlu Verifikasi
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">
                        Peserta yang hadir pada jadwal ini akan mendapatkan penambahan streak keaktifan olahraga rutin.
                    </p>

                    @if(!$isVerified)
                        <div class="mt-2 text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-200">
                            Fitur streak hanya dapat diaktifkan oleh komunitas yang telah diverifikasi resmi oleh admin.
                            <a href="{{ route('penyedia-komunitas.akun.index') }}" class="text-blue-600 font-bold hover:underline ml-1">
                                Verifikasi komunitas di menu Akun &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                <div class="shrink-0 pt-1">
                    @if($isVerified)
                        <input type="hidden" name="streak_enabled" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="streak_enabled"
                                   value="1"
                                   x-model="form.streak_enabled"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                    @else
                        <div class="w-11 h-6 bg-slate-200 rounded-full opacity-60 cursor-not-allowed flex items-center px-1">
                            <div class="h-4 w-4 bg-slate-400 rounded-full"></div>
                        </div>
                    @endif
                </div>
            </div>
            @error('streak_enabled')
                <p class="text-xs text-rose-600 mt-2 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        {{-- Toggle Aktif --}}
        <div class="p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4">
            <div>
                <span class="block text-xs font-bold text-slate-900">Status Aktif Jadwal</span>
                <span class="text-xs text-slate-500">Jadwal yang dinonaktifkan tidak akan terlihat oleh pengguna umum di jadwal agenda.</span>
            </div>
            <div class="shrink-0">
                <input type="hidden" name="is_active" value="0">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           x-model="form.is_active"
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>
</div>
