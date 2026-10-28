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
            {{-- Background Track Line (Presisi dari center lingkaran 1 ke lingkaran 3) --}}
            <div class="absolute top-[22px] -translate-y-1/2 left-[16.666%] right-[16.666%] h-1 bg-slate-200 z-0 hidden md:block rounded-full overflow-hidden">
                {{-- Animated Progress Line dengan Gradasi Royal Blue -> Cyan --}}
                <div class="h-full bg-gradient-to-r from-blue-700 via-blue-500 to-cyan-500 rounded-full transition-all duration-500 ease-in-out"
                    :style="`width: ${step === 1 ? '0%' : (step === 2 ? '50%' : '100%')}`"></div>
            </div>

            {{-- Steps Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                
                {{-- STEP 1 (Awal Gradasi: Royal Blue) --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(1)">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm relative z-10 ring-4 ring-white"
                        :class="step >= 1 
                            ? 'bg-gradient-to-br from-blue-700 to-blue-600 text-white shadow-blue-600/30' 
                            : 'bg-slate-100 text-slate-400'">
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
                            :class="step >= 1 ? 'text-blue-700' : 'text-slate-400'">
                            Informasi Event
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Detail utama & cover</p>
                    </div>
                </div>

                {{-- STEP 2 (Tengah Gradasi: Blend Blue ke Cyan) --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(2)">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm relative z-10 ring-4 ring-white"
                        :class="step >= 2 
                            ? 'bg-gradient-to-br from-blue-500 to-cyan-500 text-white shadow-cyan-500/30' 
                            : 'bg-slate-100 text-slate-400'">
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
                            :class="step >= 2 ? 'text-blue-600' : 'text-slate-400'">
                            Jadwal & Lokasi
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Waktu, tempat & batas RSVP</p>
                    </div>
                </div>

                {{-- STEP 3 (Ujung Gradasi: Cyan) --}}
                <div class="flex items-center md:flex-col md:text-center gap-4 cursor-pointer" @click="goToStep(3)">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm relative z-10 ring-4 ring-white"
                        :class="step === 3 
                            ? 'bg-gradient-to-br from-cyan-500 to-cyan-400 text-white shadow-cyan-400/30' 
                            : 'bg-slate-100 text-slate-400'">
                        <span>3</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold transition-colors"
                            :class="step === 3 ? 'text-cyan-600' : 'text-slate-400'">
                            Pendaftaran & Pembayaran
                        </h4>
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Biaya, kuota & kontak</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SERVER-SIDE ERROR ALERT --}}
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <span class="font-bold">Ada beberapa data yang perlu diperiksa:</span>
                <ul class="list-disc list-inside mt-1 font-normal text-xs text-red-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- MAIN FORM --}}
    <form id="createEventForm" action="{{ route('penyedia-komunitas.events.store') }}" method="POST" enctype="multipart/form-data" @submit="handleSubmit($event)">
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
                            { value: 'basketball', label: 'Bola Basket' },
                            { value: 'football', label: 'Sepak Bola' },
                            { value: 'cycling', label: 'Sepeda' },
                            { value: 'swimming', label: 'Berenang' },
                            { value: 'yoga', label: 'Yoga & Pilates' },
                            { value: 'tennis', label: 'Tenis Lapangan' },
                            { value: 'table_tennis', label: 'Tenis Meja' },
                            { value: 'other', label: 'Olahraga Lainnya' }
                        ],
                        get selectedLabel() {
                            if (form.sport_category === 'other') {
                                return form.other_sport_category ? ('Lainnya: ' + form.other_sport_category) : 'Olahraga Lainnya';
                            }
                            let cat = this.categories.find(c => c.value === form.sport_category);
                            return cat ? cat.label : '-- Pilih Kategori Olahraga --';
                        }
                    }">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kategori Olahraga <span class="text-red-500">*</span>
                        </label>

                        <div class="relative" @click.outside="open = false">
                            <button type="button" 
                                    @click="open = !open"
                                    class="w-full px-4 py-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer text-left shadow-sm"
                                    :class="errors.sport_category ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <span :class="!form.sport_category ? 'text-slate-400' : 'text-slate-800 font-medium'" x-text="selectedLabel"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 text-primary-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <input type="hidden" name="sport_category" :value="form.sport_category === 'other' ? (form.other_sport_category || 'other') : form.sport_category">

                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                class="absolute top-full left-0 right-0 mt-2 z-50 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl p-1.5 space-y-0.5"
                                style="display: none;">
                                <template x-for="cat in categories" :key="cat.value">
                                    <button type="button"
                                            @click="form.sport_category = (form.sport_category === cat.value ? null : cat.value); open = false;"
                                            class="w-full text-left px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 flex items-center justify-between"
                                            :class="form.sport_category === cat.value ? 'bg-primary-50/80 text-primary-700 font-bold' : 'text-slate-700 hover:bg-slate-100/80'">
                                        <span x-text="cat.label"></span>
                                        <svg x-show="form.sport_category === cat.value" class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Input Muncul Otomatis Jika Memilih Lainnya --}}
                        <div x-show="form.sport_category === 'other'" x-transition class="mt-3">
                            <input type="text" 
                                x-model="form.other_sport_category"
                                placeholder="Ketik nama cabang olahraga" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                :class="errors.sport_category ? 'border-red-400 ring-2 ring-red-100' : ''">
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
                                  placeholder="Jelaskan gambaran umum kegiatan, fasilitas yang didapat, dan informasi terkait lainnya.."
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
class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
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

                {{-- 1. Pilihan Zona Waktu (WIB, WITA, WIT) --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Pilih Zona Waktu <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button"
                                    @click="form.timezone = 'WIB'"
                                    class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                                    :class="form.timezone === 'WIB'
                                        ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300'">
                                <span>WIB</span>
                                <span class="text-[10px] font-normal" :class="form.timezone === 'WIB' ? 'text-white/80' : 'text-slate-400'">(UTC+7)</span>
                            </button>
                            <button type="button"
                                    @click="form.timezone = 'WITA'"
                                    class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                                    :class="form.timezone === 'WITA'
                                        ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300'">
                                <span>WITA</span>
                                <span class="text-[10px] font-normal" :class="form.timezone === 'WITA' ? 'text-white/80' : 'text-slate-400'">(UTC+8)</span>
                            </button>
                            <button type="button"
                                    @click="form.timezone = 'WIT'"
                                    class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                                    :class="form.timezone === 'WIT'
                                        ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-md shadow-primary-500/25 ring-2 ring-primary-500/20'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300'">
                                <span>WIT</span>
                                <span class="text-[10px] font-normal" :class="form.timezone === 'WIT' ? 'text-white/80' : 'text-slate-400'">(UTC+9)</span>
                            </button>
                        </div>
                        <input type="hidden" name="timezone" :value="form.timezone">
                    </div>

                    {{-- 2. Tanggal Event & Batas Akhir Pendaftaran (Grid 2 Kolom) --}}
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
                            <p x-show="errors.registration_close_at" x-text="errors.registration_close_at" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                    </div>

                    {{-- 3. Waktu Mulai & Waktu Selesai (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Waktu Mulai --}}
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Waktu Mulai (<span x-text="form.timezone" class="text-primary-600"></span>) <span class="text-red-500">*</span>
                            </label>
                            <input type="time"
                                   id="start_time"
                                   name="start_time"
                                   x-model="form.start_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer"
                                   :class="errors.start_time ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p x-show="errors.start_time" x-text="errors.start_time" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                        {{-- Waktu Selesai --}}
                        <div>
                            <label for="end_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Waktu Selesai (<span x-text="form.timezone" class="text-primary-600"></span>, Opsional)
                            </label>
                            <input type="time"
                                   id="end_time"
                                   name="end_time"
                                   x-model="form.end_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 cursor-pointer">
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
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
                        Lanjut ke Pendaftaran & Pembayaran
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN 3 : PENDAFTARAN & PEMBAYARAN                          --}}
        {{-- ============================================================ --}}
        <div x-show="step === 3"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                <div class="space-y-6">

                    {{-- 1. Kuota Peserta & Biaya Pendaftaran (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Kuota Peserta --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Kuota Peserta <span class="text-red-500">*</span>
                            </label>
                            
                            {{-- Pilihan Terbatas vs Unlimited --}}
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button type="button"
                                        @click="form.capacity_type = 'limited'"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all duration-200 text-center"
                                        :class="form.capacity_type === 'limited' 
                                            ? 'border-primary-500 bg-primary-50/70 text-primary-700 ring-2 ring-primary-500/20 shadow-sm' 
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                    Terbatas
                                </button>
                                <button type="button"
                                        @click="form.capacity_type = 'unlimited'; form.capacity = ''"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all duration-200 text-center"
                                        :class="form.capacity_type === 'unlimited' 
                                            ? 'border-primary-500 bg-primary-50/70 text-primary-700 ring-2 ring-primary-500/20 shadow-sm' 
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                    Tanpa Batas
                                </button>
                            </div>

                            <input type="hidden" name="capacity_type" :value="form.capacity_type">

                            {{-- Input Nominal Kuota jika Terbatas --}}
                            <div x-show="form.capacity_type === 'limited'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="relative">
                                <input type="number"
                                       id="capacity"
                                       name="capacity"
                                       x-model="form.capacity"
                                       min="1"
                                       placeholder="Contoh : 32"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                       :class="errors.capacity ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-xs font-semibold text-slate-400 pointer-events-none">
                                    Peserta
                                </span>
                            </div>
                            <p x-show="errors.capacity" x-text="errors.capacity" class="text-xs text-red-500 mt-1 font-medium"></p>
                            <p class="text-xs text-slate-400 mt-1.5" x-show="form.capacity_type === 'unlimited'">
                                Semua pendaftar dapat bergabung tanpa batas maksimal kuota.
                            </p>
                        </div>

                        {{-- Biaya Pendaftaran --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Biaya Pendaftaran <span class="text-red-500">*</span>
                            </label>

                            @php
                                $isCommunityVerified = $community?->isVerified() ?? false;
                            @endphp

                            {{-- Pilihan Gratis vs Berbayar --}}
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button type="button"
                                        @click="form.is_paid = '0'; form.registration_fee = '0'; formattedFee = ''"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all duration-200 text-center"
                                        :class="form.is_paid === '0' 
                                            ? 'border-primary-500 bg-primary-50/70 text-primary-700 ring-2 ring-primary-500/20 shadow-sm' 
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                    Gratis (Free)
                                </button>
                                <button type="button"
                                        @if($isCommunityVerified)
                                            @click="form.is_paid = '1'"
                                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all duration-200 text-center"
                                            :class="form.is_paid === '1' 
                                                ? 'border-primary-500 bg-primary-50/70 text-primary-700 ring-2 ring-primary-500/20 shadow-sm' 
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                        @else
                                            disabled
                                            title="Verifikasi komunitas Anda untuk membuka fitur tiket berbayar"
                                            class="py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-xs font-bold text-center cursor-not-allowed flex items-center justify-center gap-1.5 opacity-75"
                                        @endif>
                                    @if(!$isCommunityVerified)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    @endif
                                    Berbayar (Tiket)
                                </button>
                            </div>

                            @if(!$isCommunityVerified)
                                <div class="mb-3 p-3 rounded-xl bg-amber-50/90 border border-amber-200 text-xs flex items-start gap-2.5 text-amber-900 shadow-sm">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                                    </svg>
                                    <div class="leading-relaxed">
                                        <span class="font-bold text-amber-900">Event Berbayar Khusus Komunitas Terverifikasi.</span>
                                        <p class="text-amber-800 mt-0.5">
                                            Komunitas Anda saat ini belum terverifikasi (hanya dapat membuat event biasa/gratis dan jadwal latihan). Anda bisa melakukan verifikasi instan atau gratis (15 pengikut) di menu 
                                            <a href="{{ route('penyedia-komunitas.akun.index') }}" class="font-bold underline text-primary-700 hover:text-primary-800">Pengaturan Akun</a>.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <input type="hidden" name="is_paid" :value="form.is_paid">

                            {{-- Input Nominal Biaya jika Berbayar --}}
                            <div x-show="form.is_paid === '1'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-500 pointer-events-none">
                                    Rp
                                </span>
                                <input type="text"
                                       inputmode="numeric"
                                       id="registration_fee"
                                       :value="formattedFee"
                                       @input="handleFeeInput($event)"
                                       placeholder="Contoh : 50.000"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                       :class="errors.registration_fee ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <input type="hidden" name="registration_fee" :value="form.registration_fee">
                            </div>
                            <p x-show="errors.registration_fee" x-text="errors.registration_fee" class="text-xs text-red-500 mt-1 font-medium"></p>
                            <p class="text-xs text-slate-400 mt-1.5" x-show="form.is_paid === '0'">
                                Peserta tidak dipungut biaya registrasi untuk mengikuti kegiatan ini.
                            </p>
                        </div>

                    </div>

                    {{-- 2. Detail Pembayaran (Muncul Jika Event Berbayar) --}}
                    <div x-show="form.is_paid === '1'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/90 space-y-4">

                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                            <h3 class="text-sm font-bold text-slate-800">Instruksi & Rekening Pembayaran</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Metode Pembayaran --}}
                            <div>
                                <label for="payment_method" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="payment_method"
                                            name="payment_method"
                                            x-model="form.payment_method"
                                            class="w-full px-4 py-2.5 appearance-none rounded-xl border border-slate-200 bg-white text-slate-800 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition cursor-pointer">
                                        <option value="Transfer Bank BCA">Transfer Bank BCA</option>
                                        <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                                        <option value="Transfer Bank BRI">Transfer Bank BRI</option>
                                        <option value="Transfer Bank BNI">Transfer Bank BNI</option>
                                        <option value="QRIS">QRIS (Scan Barcode)</option>
                                        <option value="GoPay / OVO / DANA">E-Wallet (GoPay / OVO / DANA)</option>
                                        <option value="Tunai / OTS">Tunai / On The Spot (Di Lokasi)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- No. Rekening / No. Tujuan & Atas Nama --}}
                            <div>
                                <label for="payment_account" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    No. Rekening / No. Tujuan & Atas Nama <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="payment_account"
                                       name="payment_account"
                                       x-model="form.payment_account"
                                       placeholder="Contoh : BCA 842019281 a.n. Komunitas Bugar"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition"
                                       :class="errors.payment_account ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <p x-show="errors.payment_account" x-text="errors.payment_account" class="text-xs text-red-500 mt-1 font-medium"></p>
                            </div>

                        </div>

                    </div>

                    {{-- 3. Syarat & Ketentuan Peserta --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="requirements" class="block text-sm font-semibold text-slate-700">
                                Syarat & Ketentuan Peserta (Opsional)
                            </label>
                            <button type="button"
                                    @click="insertRequirementsTemplate()"
                                    class="text-xs text-primary-600 hover:text-primary-700 font-semibold inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Gunakan Template
                            </button>
                        </div>
                        <textarea id="requirements"
                                  name="requirements"
                                  rows="4"
                                  x-model="form.requirements"
                                  placeholder="Contoh format :&#10;1. Terbuka untuk umum / member komunitas&#10;2. Wajib mengenakan pakaian dan sepatu olahraga yang sesuai&#10;3. Membawa botol minum pribadi (tumbler)&#10;4. Kondisi tubuh dalam keadaan sehat dan fit"
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"></textarea>
                        <p class="text-xs text-slate-400 mt-1.5">Syarat khusus seperti batasan umur, dresscode, atau kelengkapan yang wajib dibawa.</p>
                    </div>

                    {{-- 4. Kontak Penyelenggara --}}
                    <div>
                        <label for="organizer_contact" class="block text-sm font-semibold text-slate-700 mb-2">
                            Kontak Penyelenggara / Narahubung <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="organizer_contact"
                                   name="organizer_contact"
                                   x-model="form.organizer_contact"
                                   placeholder="Contoh : WhatsApp 0812-3456-7890 (Admin Budi) / Instagram @komunitas_sport"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                   :class="errors.organizer_contact ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                        </div>
                        <p x-show="errors.organizer_contact" x-text="errors.organizer_contact" class="text-xs text-red-500 mt-1 font-medium"></p>
                    </div>

                    {{-- Ringkasan Mini Sebelum Submit --}}
                    <div class="p-4 rounded-xl bg-primary-50/50 border border-primary-100 text-xs text-slate-600 flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="font-bold text-primary-800">Periksa Kembali Data:</span>
                            Event yang dipublikasikan akan langsung tampil di dashboard dan dapat dilihat oleh calon peserta di portal publik Bugar.in.
                        </div>
                    </div>

                </div>

                {{-- Action / Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <button type="button"
                            @click="goToStep(2)"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Sebelumnya
                    </button>
                    
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/25 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        <template x-if="!isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Publikasikan Event Sekarang
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </template>
                    </button>
                </div>

            </div>

        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function eventWizard() {
    return {
        step: {{ $errors->hasAny(['capacity', 'registration_fee', 'payment_account', 'organizer_contact']) ? 3 : ($errors->hasAny(['event_date', 'registration_close_at', 'start_time', 'location', 'maps_url']) ? 2 : 1) }},
        isDragging: false,
        previewUrl: null,
        errors: {},
        formattedFee: '',
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
        isSubmitting: false,
        form: {
            title: @json(old('title', '')),
            type: @json(old('type', 'fun_match')),
            sport_category: @json(old('sport_category', $community->sport_category ?? '')),
            other_sport_category: @json(old('other_sport_category', '')),
            description: @json(old('description', '')),
            rundown: @json(old('rundown', '')),
            cover_image: null,
            event_date: @json(old('event_date', '')),
            registration_close_at: @json(old('registration_close_at', '')),
            timezone: @json(old('timezone', 'WIB')),
            start_time: @json(old('start_time', '')),
            end_time: @json(old('end_time', '')),
            location: @json(old('location', '')),
            maps_url: @json(old('maps_url', '')),
            capacity_type: @json(old('capacity_type', 'limited')),
            capacity: @json(old('capacity', '')),
            is_paid: @json(old('is_paid', '0')),
            registration_fee: @json(old('registration_fee', '')),
            payment_method: @json(old('payment_method', 'Transfer Bank BCA')),
            payment_account: @json(old('payment_account', '')),
            requirements: @json(old('requirements', '')),
            organizer_contact: @json(old('organizer_contact', ''))
        },
        init() {
            const predefined = ['badminton', 'running', 'futsal', 'basketball', 'football', 'cycling', 'swimming', 'yoga', 'tennis', 'table_tennis'];
            if (this.form.sport_category && !predefined.includes(this.form.sport_category)) {
                this.form.other_sport_category = this.form.sport_category;
                this.form.sport_category = 'other';
            }
            if (this.form.registration_fee && Number(this.form.registration_fee) > 0) {
                this.formattedFee = Number(this.form.registration_fee).toLocaleString('id-ID');
            }
        },
        handleFeeInput(e) {
            const raw = e.target.value.replace(/[^0-9]/g, '');
            this.form.registration_fee = raw;
            this.formattedFee = raw ? Number(raw).toLocaleString('id-ID') : '';
            e.target.value = this.formattedFee;
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
                } else if (this.form.sport_category === 'other' && (!this.form.other_sport_category || !this.form.other_sport_category.trim())) {
                    this.errors.sport_category = 'Ketik nama cabang olahraga jika memilih Olahraga Lainnya.';
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
        },
        insertRequirementsTemplate() {
            this.form.requirements = "1. Terbuka untuk umum / member komunitas\n2. Wajib mengenakan pakaian dan sepatu olahraga yang sesuai\n3. Membawa botol minum pribadi (tumbler)\n4. Kondisi tubuh dalam keadaan sehat dan fit\n5. Hadir di lokasi minimal 15 menit sebelum kegiatan dimulai";
        },
        handleSubmit(event) {
            this.errors = {};

            // Validasi Step 1
            if (!this.form.title || !this.form.title.trim()) {
                this.errors.title = 'Nama event wajib diisi.';
            }
            if (!this.form.type) {
                this.errors.type = 'Pilih salah satu jenis event.';
            }
            if (!this.form.sport_category) {
                this.errors.sport_category = 'Pilih kategori olahraga.';
            } else if (this.form.sport_category === 'other' && (!this.form.other_sport_category || !this.form.other_sport_category.trim())) {
                this.errors.sport_category = 'Ketik nama cabang olahraga jika memilih Olahraga Lainnya.';
            }
            if (!this.form.description || !this.form.description.trim()) {
                this.errors.description = 'Deskripsi event wajib diisi.';
            }
            if (!this.previewUrl && !this.form.cover_image) {
                this.errors.cover_image = 'Cover banner event wajib diunggah.';
            }

            // Validasi Step 2
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

            // Validasi Step 3
            if (this.form.capacity_type === 'limited' && (!this.form.capacity || this.form.capacity <= 0)) {
                this.errors.capacity = 'Kuota peserta wajib diisi minimal 1.';
            }
            if (this.form.is_paid === '1') {
                if (!this.form.registration_fee || Number(this.form.registration_fee) <= 0) {
                    this.errors.registration_fee = 'Nominal biaya pendaftaran wajib diisi.';
                }
                if (!this.form.payment_account || !this.form.payment_account.trim()) {
                    this.errors.payment_account = 'Nomor rekening / tujuan pembayaran wajib diisi.';
                }
            }
            if (!this.form.organizer_contact || !this.form.organizer_contact.trim()) {
                this.errors.organizer_contact = 'Kontak narahubung panitia wajib diisi.';
            }

            if (Object.keys(this.errors).length > 0) {
                event.preventDefault();
                if (this.errors.title || this.errors.type || this.errors.sport_category || this.errors.description || this.errors.cover_image) {
                    this.goToStep(1);
                } else if (this.errors.event_date || this.errors.registration_close_at || this.errors.start_time || this.errors.location) {
                    this.goToStep(2);
                }
                this.$nextTick(() => {
                    const firstError = document.querySelector('[class*="border-red-400"]');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
                return false;
            }

            this.isSubmitting = true;
            return true;
        }
    };
}
</script>
@endpush
