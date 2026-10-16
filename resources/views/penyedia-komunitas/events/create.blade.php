@extends('layouts.penyedia-komunitas')

@section('title', 'Buat Event Baru - Penyedia Komunitas')

@section('content')
<div class="max-w-5xl mx-auto pb-16" x-data="eventWizard()">

    {{-- Breadcrumb & Back --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('penyedia-komunitas.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
            Buat Event Komunitas Baru
        </h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">
            Publikasikan agenda olahraga komunitasmu untuk menjaring lebih banyak peserta dan kembangkan jaringan komunitas.
        </p>
    </div>

    {{-- WIZARD STEPPER & ANIMATED PROGRESS BAR --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm">
        <div class="relative">
            {{-- Background Track Line --}}
            <div class="absolute top-5 left-10 right-10 h-1 bg-slate-100 rounded-full -z-0 hidden md:block"></div>
            
            {{-- Animated Progress Line --}}
            <div class="absolute top-5 left-10 h-1 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full -z-0 transition-all duration-500 ease-in-out hidden md:block"
                 :style="'width: ' + ((step - 1) / 2 * 80) + '%;'"></div>

            {{-- Steps Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                
                {{-- STEP 1 --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(1)">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                         :class="step === 1 
                            ? 'bg-gradient-to-br from-primary-500 to-primary-600 text-white ring-4 ring-primary-100 shadow-primary-500/25' 
                            : (step > 1 ? 'bg-primary-500 text-white' : 'bg-slate-100 text-slate-400')">
                        <template x-if="step > 1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="step <= 1">
                            <span>1</span>
                        </template>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold transition-colors"
                            :class="step === 1 ? 'text-primary-700' : (step > 1 ? 'text-slate-800' : 'text-slate-400')">
                            Informasi Event
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Detail utama & cover</p>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(2)">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                         :class="step === 2 
                            ? 'bg-gradient-to-br from-primary-500 to-primary-600 text-white ring-4 ring-primary-100 shadow-primary-500/25' 
                            : (step > 2 ? 'bg-primary-500 text-white' : 'bg-slate-100 text-slate-400')">
                        <template x-if="step > 2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="step <= 2">
                            <span>2</span>
                        </template>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold transition-colors"
                            :class="step === 2 ? 'text-primary-700' : (step > 2 ? 'text-slate-800' : 'text-slate-400')">
                            Jadwal & Lokasi
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Waktu, tempat & batas RSVP</p>
                    </div>
                </div>

                {{-- STEP 3 --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(3)">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                         :class="step === 3 
                            ? 'bg-gradient-to-br from-primary-500 to-primary-600 text-white ring-4 ring-primary-100 shadow-primary-500/25' 
                            : 'bg-slate-100 text-slate-400'">
                        <span>3</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold transition-colors"
                            :class="step === 3 ? 'text-primary-700' : 'text-slate-400'">
                            Pendaftaran & Pembayaran
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Biaya, kuota & kontak</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MAIN FORM --}}
    <form id="createEventForm" action="#" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ============================================================ --}}
        {{-- BAGIAN 1 : INFORMASI EVENT                                    --}}
        {{-- ============================================================ --}}
        <div x-show="step === 1"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                {{-- Fields Container --}}
                <div class="mt-6 space-y-6">

                    {{-- 1. Nama Event --}}
                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Event <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               name="title"
                               x-model="form.title"
                               placeholder="Contoh : Bugar Fun Run 5K Bersama Komunitas"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                               :class="errors.title ? 'border-red-400 ring-2 ring-red-100' : ''"
                               required>
                        <p x-show="errors.title" x-text="errors.title" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                    </div>

                   {{-- Kategori Olahraga --}}
                    <div class="mb-6" x-data="{ 
                        open: false, 
                        categories: [
                            { value: 'badminton', label: 'Badminton / Bulu Tangkis' },
                            { value: 'running', label: 'Running / Lari' },
                            { value: 'futsal', label: 'Futsal' },
                            { value: 'basketball', label: 'Basket' },
                            { value: 'football', label: 'Sepak Bola' },
                            { value: 'cycling', label: 'Sepeda' },
                            { value: 'swimming', label: 'Berenang' },
                            { value: 'yoga', label: 'Yoga & Pilates' },
                            { value: 'tennis', label: 'Tenis Lapangan' },
                            { value: 'table_tennis', label: 'Tenis Meja' },
                            { value: 'other', label: 'Olahraga Lainnya' }
                        ],
                        get selectedLabel() {
                            let cat = this.categories.find(c => c.value === form.sport_category);
                            return cat ? cat.label : 'Pilih Kategori Olahraga';
                        }
                    }">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kategori Olahraga <span class="text-red-500">*</span>
                        </label>

                        <div class="relative" @click.outside="open = false">
                            {{-- Trigger Button --}}
                            <button type="button" 
                                    @click="open = !open"
                                    class="w-full px-4 py-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer text-left shadow-sm"
                                    :class="errors.sport_category ? 'border-red-400 ring-2 ring-red-100' : ''">
                                
                                <span :class="!form.sport_category ? 'text-slate-400' : 'text-slate-800 font-medium'" 
                                    x-text="selectedLabel"></span>
                                
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="w-4 h-4 text-slate-400 transition-transform duration-300" 
                                    :class="open ? 'rotate-180 text-primary-500' : ''" 
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <input type="hidden" name="sport_category" :value="form.sport_category ?? ''">

                            {{-- Dropdown Menu --}}
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                class="absolute top-full left-0 right-0 mt-2 z-50 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl p-1.5 space-y-0.5 custom-scrollbar"
                                style="display: none;">
                                
                                <template x-for="cat in categories" :key="cat.value">
                                    <button type="button"
                                            @click="form.sport_category = (form.sport_category === cat.value ? null : cat.value); open = false;"
                                            class="w-full text-left px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 flex items-center justify-between"
                                            :class="form.sport_category === cat.value 
                                                ? 'bg-primary-50/80 text-primary-700 font-bold' 
                                                : 'text-slate-700 hover:bg-slate-100/80'">
                                        <span x-text="cat.label"></span>
                                        <svg x-show="form.sport_category === cat.value" class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <p x-show="errors.sport_category" x-text="errors.sport_category" class="text-xs text-red-500 mt-1 font-medium"></p>
                    </div>

                    {{-- Jenis Event --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Jenis Event <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="flex flex-wrap gap-2.5">
                            <template x-for="item in eventTypes" :key="item.value">
                                <button type="button"
                                        @click="form.type = (form.type === item.value ? null : item.value)"
                                        class="px-5 py-2.5 rounded-full border text-xs font-semibold flex items-center gap-2 transition-all duration-200 shadow-sm"
                                        :class="form.type === item.value 
                                            ? 'border-primary-500 bg-primary-50/70 text-primary-800 ring-2 ring-primary-500/20' 
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50/60'">
                                    <span x-html="item.icon" class="w-4 h-4" :class="form.type === item.value ? 'text-primary-600' : 'text-slate-400'"></span>
                                    <span x-text="item.label" class="font-bold"></span>
                                </button>
                            </template>
                        </div>
                        
                        <input type="hidden" name="type" :value="form.type ?? ''">
                        <p x-show="errors.type" x-text="errors.type" class="text-xs text-red-500 mt-2 font-medium"></p>
                    </div>

                    {{-- 3. Deskripsi Event --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700">
                                Deskripsi Event <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs text-slate-400" x-text="(form.description ? form.description.length : 0) + ' karakter'"></span>
                        </div>
                        <textarea id="description"
                                  name="description"
                                  rows="4"
                                  x-model="form.description"
                                  placeholder="Jelaskan gambaran umum kegiatan, tingkat kemampuan peserta (pemula / umum), fasilitas yang didapat, peraturan pakaian/alat..."
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                  :class="errors.description ? 'border-red-400 ring-2 ring-red-100' : ''"></textarea>
                        <p x-show="errors.description" x-text="errors.description" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                    </div>

                    {{-- 4. Rundown / Susunan Acara --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="rundown" class="block text-sm font-semibold text-slate-700">
                                Rundown / Susunan Acara (Opsional)
                            </label>
                            <button type="button"
                                    @click="insertRundownTemplate()"
                                    class="text-xs text-primary-600 hover:text-primary-700 font-semibold inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Gunakan Template
                            </button>
                        </div>
                        <textarea id="rundown"
                                  name="rundown"
                                  rows="4"
                                  x-model="form.rundown"
                                  placeholder="Contoh format :&#10;06.00 - 06.30 : Registrasi Ulang & Pemanasan&#10;06.30 - 08.00 : Sesi Olahraga Bersama&#10;08.00 - 08.30 : Pembagian Hadiah &Foto Bersama"
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm font-mono placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"></textarea>
                        <p class="text-xs text-slate-400 mt-1.5">Informasikan alur kegiatan agar peserta hadir tepat waktu.</p>
                    </div>

                    {{-- 5. Cover Banner --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Cover Banner Event <span class="text-red-500">*</span>
                        </label>
                        
                        {{-- Dropzone / Upload Area --}}
                        <div class="relative rounded-2xl border-2 border-dashed border-slate-200 hover:border-primary-400 bg-slate-50/50 hover:bg-primary-50/20 transition-all duration-200 p-6 text-center cursor-pointer group"
                             :class="previewUrl ? 'border-primary-400 bg-white' : ''"
                             @click="$refs.coverFileInput.click()"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="handleDrop($event)">

                            <input type="file"
                                   id="cover_image"
                                   name="cover_image"
                                   x-ref="coverFileInput"
                                   @change="handleFileSelect($event)"
                                   accept="image/png, image/jpeg, image/webp"
                                   class="hidden">

                            {{-- PREVIEW JIKA ADA FOTO --}}
                            <template x-if="previewUrl">
                                <div class="relative w-full aspect-[21/9] rounded-xl overflow-hidden shadow-inner border border-slate-200">
                                    <img :src="previewUrl" alt="Cover Preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                        <button type="button"
                                                @click.stop="$refs.coverFileInput.click()"
                                                class="px-4 py-2 rounded-lg bg-white/95 text-slate-800 text-xs font-bold hover:bg-white shadow">
                                            Ganti Gambar
                                        </button>
                                        <button type="button"
                                                @click.stop="removeImage()"
                                                class="px-4 py-2 rounded-lg bg-red-500/90 text-white text-xs font-bold hover:bg-red-600 shadow">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- PLACEHOLDER JIKA BELUM ADA FOTO --}}
                            <template x-if="!previewUrl">
                                <div class="py-4 flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-primary-500 shadow-sm group-hover:scale-105 group-hover:border-primary-300 transition-all duration-200 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        <span class="text-primary-600 underline">Klik untuk upload</span> atau drag & drop banner di sini
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        Format PNG, JPG, atau WEBP. Disarankan rasio 16:9 / 2:1 (Maks. 2MB).
                                    </p>
                                </div>
                            </template>

                        </div>
                        <p x-show="errors.cover_image" x-text="errors.cover_image" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                    </div>

                </div>

                {{-- Action / Next Button --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('penyedia-komunitas.dashboard') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="button"
                            @click="validateAndNext(1)"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-bold shadow-lg shadow-primary-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
                        Lanjut ke Jadwal & Lokasi
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN 2 : JADWAL & LOKASI                                   --}}
        {{-- ============================================================ --}}
        <div x-show="step === 2"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                <div class="space-y-6">

                    {{-- 1. Tanggal Event & Batas Akhir Pendaftaran (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Tanggal Pelaksanaan --}}
                        <div>
                            <label for="event_date" class="block text-sm font-semibold text-slate-700 mb-2">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   id="event_date"
                                   name="event_date"
                                   x-model="form.event_date"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer"
                                   :class="errors.event_date ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p class="text-xs text-slate-400 mt-1.5">Hari H diselenggarakannya kegiatan olahraga.</p>
                            <p x-show="errors.event_date" x-text="errors.event_date" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                        {{-- Batas Akhir Pendaftaran --}}
                        <div>
                            <label for="registration_close_at" class="block text-sm font-semibold text-slate-700 mb-2">
                                Batas Akhir Pendaftaran <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local"
                                   id="registration_close_at"
                                   name="registration_close_at"
                                   x-model="form.registration_close_at"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer"
                                   :class="errors.registration_close_at ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p class="text-xs text-slate-400 mt-1.5">Pendaftaran otomatis ditutup setelah waktu ini tercapai.</p>
                            <p x-show="errors.registration_close_at" x-text="errors.registration_close_at" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                    </div>

                    {{-- 2. Waktu Mulai & Waktu Selesai (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Waktu Mulai --}}
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Waktu Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="time"
                                   id="start_time"
                                   name="start_time"
                                   x-model="form.start_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer"
                                   :class="errors.start_time ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p class="text-xs text-slate-400 mt-1.5">Jam dimulainya rangkaian acara</p>
                            <p x-show="errors.start_time" x-text="errors.start_time" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                        {{-- Waktu Selesai --}}
                        <div>
                            <label for="end_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Waktu Selesai (Opsional)
                            </label>
                            <input type="time"
                                   id="end_time"
                                   name="end_time"
                                   x-model="form.end_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer">
                            <p class="text-xs text-slate-400 mt-1.5">Estimasi waktu berakhirnya acara.</p>
                        </div>

                    </div>

                    {{-- 3. Nama Lokasi & Venue --}}
                    <div>
                        <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Tempat / Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="location"
                               name="location"
                               x-model="form.location"
                               placeholder="Contoh : Lapangan Futsal Maestro, Jl. Tubagus Ismail No. 12, Bandung"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                               :class="errors.location ? 'border-red-400 ring-2 ring-red-100' : ''"
                               required>
                        <p class="text-xs text-slate-400 mt-1.5">Tuliskan nama tempat, gedung, lapangan, beserta detail patokan jika ada.</p>
                        <p x-show="errors.location" x-text="errors.location" class="text-xs text-red-500 mt-1 font-medium"></p>
                    </div>

                    {{-- 4. Tautan Google Maps --}}
                    <div>
                        <label for="maps_url" class="block text-sm font-semibold text-slate-700 mb-2">
                            Tautan Google Maps (Opsional)
                        </label>
                        <div class="relative">
                            <input type="url"
                                   id="maps_url"
                                   name="maps_url"
                                   x-model="form.maps_url"
                                   placeholder="Contoh : https://maps.app.goo.gl/..."
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                    </div>

                </div>

                {{-- Action / Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <button type="button"
                            @click="goToStep(1)"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Sebelumnya
                    </button>
                    <button type="button"
                            @click="validateAndNext(2)"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-bold shadow-lg shadow-primary-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
                        Lanjut ke Pendaftaran & Pembayaran
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN 3: PENDAFTARAN & PEMBAYARAN (Placeholder untuk Langkah Berikutnya) --}}
        {{-- ============================================================ --}}
        <div x-show="step === 3" class="space-y-6">
            {{-- Konten Bagian 3 akan di-generate pada tahap berikutnya --}}
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function eventWizard() {
    return {
        step: 1,
        isDragging: false,
        previewUrl: null,
        errors: {},
        eventTypes: [
            { 
                value: 'fun_match', 
                label: 'Fun Match',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
            },
            { 
                value: 'tournament', 
                label: 'Tournament',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>'
            },
            { 
                value: 'gathering', 
                label: 'Gathering',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
            },
            { 
                value: 'conference', 
                label: 'Conference',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>'
            },
            { 
                value: 'other', 
                label: 'Lainnya',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" /></svg>'
            }
        ],
        form: {
            title: '',
            type: 'fun_match',
            sport_category: '{{ $community->sport_category ?? "" }}',
            description: '',
            rundown: '',
            cover_image: null,
            event_date: '',
            registration_close_at: '',
            start_time: '',
            end_time: '',
            location: '',
            maps_url: ''
        },
        goToStep(targetStep) {
            this.step = targetStep;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        validateAndNext(currentStep) {
            this.errors = {};
            if (currentStep === 1) {
                if (!this.form.title || !this.form.title.trim()) {
                    this.errors.title = 'Nama event wajib diisi.';
                }
                if (!this.form.type) {
                    this.errors.type = 'Pilih salah satu jenis event.';
                }
                if (!this.form.sport_category) {
                    this.errors.sport_category = 'Pilih kategori olahraga.';
                }
                if (!this.form.description || !this.form.description.trim()) {
                    this.errors.description = 'Deskripsi event wajib diisi.';
                }
                if (!this.previewUrl && !this.form.cover_image) {
                    this.errors.cover_image = 'Cover banner event wajib diunggah.';
                }

                if (Object.keys(this.errors).length === 0) {
                    this.goToStep(2);
                } else {
                    this.$nextTick(() => {
                        const firstError = document.querySelector('[class*="border-red-400"]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            } else if (currentStep === 2) {
                if (!this.form.event_date) {
                    this.errors.event_date = 'Tanggal pelaksanaan event wajib diisi.';
                }
                if (!this.form.registration_close_at) {
                    this.errors.registration_close_at = 'Batas akhir pendaftaran wajib diisi.';
                }
                if (!this.form.start_time) {
                    this.errors.start_time = 'Waktu mulai event wajib diisi.';
                }
                if (!this.form.location || !this.form.location.trim()) {
                    this.errors.location = 'Nama tempat / lokasi event wajib diisi.';
                }

                // Validasi logis: batas pendaftaran tidak boleh melewati hari H
                if (this.form.event_date && this.form.registration_close_at) {
                    const eventDate = new Date(this.form.event_date);
                    const regClose = new Date(this.form.registration_close_at);
                    if (regClose > eventDate) {
                        this.errors.registration_close_at = 'Batas pendaftaran tidak boleh melewati tanggal acara.';
                    }
                }

                if (Object.keys(this.errors).length === 0) {
                    this.goToStep(3);
                } else {
                    this.$nextTick(() => {
                        const firstError = document.querySelector('[class*="border-red-400"]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            }
        },
        handleFileSelect(event) {
            const file = event.target.files[0];
            this.processFile(file);
        },
        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.$refs.coverFileInput.files = event.dataTransfer.files;
                this.processFile(file);
            }
        },
        processFile(file) {
            if (!file) return;
            if (!file.type.match('image.*')) {
                this.errors.cover_image = 'File harus berupa gambar (JPG, PNG, atau WEBP).';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                this.errors.cover_image = 'Ukuran banner maksimal 2MB.';
                return;
            }
            this.errors.cover_image = null;
            this.form.cover_image = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        removeImage() {
            this.previewUrl = null;
            this.form.cover_image = null;
            this.$refs.coverFileInput.value = '';
        },
        insertRundownTemplate() {
            this.form.rundown = "06.00 - 06.30 : Registrasi Ulang & Pemanasan\n06.30 - 08.00 : Sesi Olahraga / Pertandingan\n08.00 - 08.30 : Cooling Down & Sesi Foto Bersama\n08.30 - 09.00 : Ramah Tamah & Penutupan";
        }
    };
}
</script>
@endpush
