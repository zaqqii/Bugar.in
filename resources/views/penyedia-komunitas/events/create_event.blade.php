@extends('layouts.penyedia-komunitas')

@php
    $isEdit = isset($event) && $event !== null;
    $parsed = $parsed ?? [];

    $initialPreviewUrl = null;
    if ($isEdit && !empty($event->cover_image)) {
        $initialPreviewUrl = \Illuminate\Support\Str::startsWith($event->cover_image, ['http://', 'https://'])
            ? $event->cover_image
            : \Illuminate\Support\Facades\Storage::url($event->cover_image);
    }

    $initialForm = [
        'title' => old('title', $event->title ?? ''),
        'type' => old('type', $event->type ?? 'fun_match'),
        'description' => old('description', $parsed['description'] ?? $event->description ?? ''),
        'rundown' => old('rundown', $parsed['rundown'] ?? ''),
        'cover_image' => null,
        'event_date' => old('event_date', isset($event->event_date) ? $event->event_date->format('Y-m-d') : ''),
        'registration_close_at' => old('registration_close_at', isset($event->registration_close_at) ? $event->registration_close_at->format('Y-m-d') : ''),
        'timezone' => old('timezone', $parsed['timezone'] ?? 'WIB'),
        'start_time' => old('start_time', isset($event->start_time) ? substr($event->start_time, 0, 5) : ''),
        'end_time' => old('end_time', isset($event->end_time) ? substr($event->end_time, 0, 5) : ''),
        'location' => old('location', $event->location ?? ''),
        'maps_url' => old('maps_url', $event->maps_url ?? ''),
        'capacity_type' => old('capacity_type', ($isEdit && $event->capacity === null) ? 'unlimited' : 'limited'),
        'capacity' => old('capacity', $event->capacity ?? ''),
        'is_paid' => old('is_paid', ($isEdit && (float)($event->registration_fee ?? 0) > 0) ? '1' : '0'),
        'registration_fee' => old('registration_fee', isset($event->registration_fee) ? (int)$event->registration_fee : ''),
        'payment_method' => old('payment_method', $parsed['payment_method'] ?? 'Transfer Bank BCA'),
        'payment_account' => old('payment_account', $parsed['payment_account'] ?? ''),
        'requirements' => old('requirements', $parsed['requirements'] ?? $event->requirements ?? ''),
        'organizer_contact' => old('organizer_contact', $parsed['organizer_contact'] ?? '')
    ];
@endphp

@section('title', ($isEdit ? 'Edit Event: ' . $event->title : 'Buat Event Baru') . ' - Penyedia Komunitas')

@section('content')
<div class="max-w-5xl mx-auto pb-16" x-data="eventWizard()">

    {{-- Breadcrumb & Back --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('penyedia-komunitas.events.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Event
        </a>

        @if(!$isEdit || $event->status === 'draft')
            <button type="button"
                    @click="submitWithAction('draft')"
                    :disabled="isSubmitting"
                    class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm inline-flex items-center gap-2 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Simpan Draft</span>
            </button>
        @endif
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
            <span class="w-2 h-2 rounded-full bg-primary-500"></span>
            {{ strtoupper($community->sport_category ?? 'Olahraga') }} &bull; {{ strtoupper($community->name) }}
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ $isEdit ? 'Edit Agenda Event' : 'Buat Event Komunitas Baru' }}
        </h1>
        <p class="text-slate-500 mt-1 text-sm md:text-base">
            {{ $isEdit ? 'Perbarui informasi agenda, waktu, lokasi, dan detail tiket event komunitas Anda.' : 'Publikasikan agenda olahraga komunitasmu untuk menjaring lebih banyak peserta dan kembangkan jaringan komunitas.' }}
        </p>
    </div>

    {{-- WIZARD STEPPER & ANIMATED PROGRESS BAR --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm">
        <div class="relative">
            {{-- Background Track Line --}}
            <div class="absolute top-[22px] -translate-y-1/2 left-[16.666%] right-[16.666%] h-1 bg-slate-200 z-0 hidden md:block rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-700 via-blue-500 to-cyan-500 rounded-full transition-all duration-500 ease-in-out"
                    :style="`width: ${step === 1 ? '0%' : (step === 2 ? '50%' : '100%')}`"></div>
            </div>

            {{-- Steps Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                
                {{-- STEP 1 --}}
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

                {{-- STEP 2 --}}
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
                        <p class="text-xs text-slate-400 hidden md:block mt-0.5">Waktu & tempat event</p>
                    </div>
                </div>

                {{-- STEP 3 --}}
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
    <form id="eventForm"
          action="{{ $isEdit ? route('penyedia-komunitas.events.update', $event) : route('penyedia-komunitas.events.store') }}"
          method="POST"
          enctype="multipart/form-data"
          @submit="handleSubmit($event)">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="action" :value="submitAction">
        <input type="hidden" name="status" :value="submitAction === 'draft' ? 'draft' : 'published'">

        {{-- ============================================================ --}}
        {{-- BAGIAN 1 : INFORMASI EVENT                                    --}}
        {{-- ============================================================ --}}
        <div x-show="step === 1"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                {{-- Section Title --}}
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">
                        01
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Informasi Dasar &amp; Visual Event</h2>
                        <p class="text-xs text-slate-400">Tentukan nama acara, jenis kegiatan, dan banner pemikat calon peserta.</p>
                    </div>
                </div>

                {{-- Fields Container --}}
                <div class="mt-6 space-y-6">

                    {{-- 1. Nama Event --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="title" class="block text-sm font-semibold text-slate-700">
                                Nama Event <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs font-semibold tabular-nums"
                                  :class="(form.title ? form.title.length : 0) > 80 ? 'text-rose-600 font-bold' : 'text-slate-400'">
                                <span x-text="form.title ? form.title.length : 0">0</span>/80 karakter
                            </span>
                        </div>
                        <input type="text"
                               id="title"
                               name="title"
                               x-model="form.title"
                               maxlength="80"
                               placeholder="Contoh : Bugar Fun Run 5K Bersama Komunitas"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                               :class="errors.title ? 'border-red-400 ring-2 ring-red-100' : ''"
                               required>
                        <div class="flex items-center justify-between mt-1.5 text-xs">
                            <p x-show="errors.title" x-text="errors.title" class="text-red-500 font-medium"></p>
                            <p class="text-slate-400 ml-auto text-[11px]">Batas maksimal 80 karakter agar judul tampil rapi dan proporsional.</p>
                        </div>
                    </div>

                    {{-- Info Cabang Olahraga (Otomatis dari Komunitas) --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabang Olahraga Event</span>
                            <span class="text-sm font-bold text-slate-800">{{ ucfirst($community->sport_category ?? 'Olahraga') }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold self-start sm:self-auto">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            Ditentukan dari profil komunitas
                        </span>
                    </div>

                    {{-- 2. Jenis Event --}}
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
                                  placeholder="Contoh format :&#10;06.00 - 06.30 : Registrasi Ulang & Pemanasan&#10;06.30 - 08.00 : Sesi Olahraga Bersama&#10;08.00 - 08.30 : Cooling Down & Sesi Foto Bersama"
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm font-mono placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"></textarea>
                        <p class="text-xs text-slate-400 mt-1.5">Informasikan alur kegiatan agar peserta hadir tepat waktu.</p>
                    </div>

                    {{-- 5. Cover Banner --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Cover Banner Event <span class="text-red-500" x-show="!isEdit">*</span>
                            @if($isEdit)
                                <span class="text-xs text-slate-400 font-normal ml-2">(Kosongkan jika tidak ingin mengubah cover)</span>
                            @endif
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
                            <div x-show="previewUrl" class="space-y-4" style="display: none;">
                                <div class="relative max-w-md mx-auto aspect-video rounded-xl overflow-hidden border border-slate-200 shadow-md">
                                    <img :src="previewUrl" alt="Cover Preview" class="w-full h-full object-cover">
                                    <button type="button"
                                            @click.stop="removeImage()"
                                            class="absolute top-2 right-2 p-1.5 rounded-lg bg-black/60 hover:bg-black/80 text-white transition-colors"
                                            title="Hapus foto">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 font-medium">Klik atau seret gambar baru untuk mengganti cover banner.</p>
                            </div>

                            {{-- PLACEHOLDER JIKA BELUM ADA FOTO --}}
                            <div x-show="!previewUrl" class="space-y-3">
                                <div class="w-12 h-12 rounded-full bg-primary-50 text-primary-600 mx-auto flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-700 block">Pilih foto atau seret ke sini</span>
                                    <span class="text-xs text-slate-400 mt-1 block">Format didukung: JPG, PNG, atau WEBP (Maksimal 2MB). Disarankan rasio 16:9.</span>
                                </div>
                            </div>

                        </div>
                        <p x-show="errors.cover_image" x-text="errors.cover_image" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                    </div>

                </div>

                {{-- Action / Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <div></div>
                    <button type="button"
                            @click="validateAndNext(1)"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
                        <span>Lanjut ke Jadwal &amp; Lokasi</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN 2 : JADWAL & LOKASI                                     --}}
        {{-- ============================================================ --}}
        <div x-show="step === 2"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6"
             style="display: none;">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                {{-- Section Title --}}
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                        02
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Waktu &amp; Lokasi Pelaksanaan</h2>
                        <p class="text-xs text-slate-400">Atur jadwal pelaksanaan event, batas penutupan pendaftaran, dan titik temu lokasi.</p>
                    </div>
                </div>

                {{-- Fields Container --}}
                <div class="mt-6 space-y-6">

                    {{-- Tanggal & Batas Registrasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Tanggal Event --}}
                        <div>
                            <label for="event_date" class="block text-sm font-semibold text-slate-700 mb-2">
                                Tanggal Pelaksanaan Event <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   id="event_date"
                                   name="event_date"
                                   x-model="form.event_date"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                   :class="errors.event_date ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p x-show="errors.event_date" x-text="errors.event_date" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                        </div>

                        {{-- Batas Akhir Registrasi --}}
                        <div>
                            <label for="registration_close_at" class="block text-sm font-semibold text-slate-700 mb-2">
                                Batas Penutupan Pendaftaran <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   id="registration_close_at"
                                   name="registration_close_at"
                                   x-model="form.registration_close_at"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                   :class="errors.registration_close_at ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p x-show="errors.registration_close_at" x-text="errors.registration_close_at" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                        </div>

                    </div>

                    {{-- Jam Mulai, Selesai & Zona Waktu --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        {{-- Jam Mulai --}}
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Jam Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="time"
                                   id="start_time"
                                   name="start_time"
                                   x-model="form.start_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                   :class="errors.start_time ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <p x-show="errors.start_time" x-text="errors.start_time" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                        </div>

                        {{-- Jam Selesai --}}
                        <div>
                            <label for="end_time" class="block text-sm font-semibold text-slate-700 mb-2">
                                Jam Selesai (Opsional)
                            </label>
                            <input type="time"
                                   id="end_time"
                                   name="end_time"
                                   x-model="form.end_time"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200">
                        </div>

                        {{-- Zona Waktu --}}
                        <div>
                            <label for="timezone" class="block text-sm font-semibold text-slate-700 mb-2">
                                Zona Waktu
                            </label>
                            <select id="timezone"
                                    name="timezone"
                                    x-model="form.timezone"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200">
                                <option value="WIB">WIB (Waktu Indonesia Barat)</option>
                                <option value="WITA">WITA (Waktu Indonesia Tengah)</option>
                                <option value="WIT">WIT (Waktu Indonesia Timur)</option>
                            </select>
                        </div>

                    </div>

                    {{-- Nama Tempat / Lokasi --}}
                    <div>
                        <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Tempat / Lokasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="location"
                                   name="location"
                                   x-model="form.location"
                                   placeholder="Contoh : Lapangan Futsal Champion Court 2, Jakarta Barat"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                   :class="errors.location ? 'border-red-400 ring-2 ring-red-100' : ''"
                                   required>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <p x-show="errors.location" x-text="errors.location" class="text-xs text-red-500 mt-1.5 font-medium"></p>
                    </div>

                    {{-- Google Maps URL --}}
                    <div>
                        <label for="maps_url" class="block text-sm font-semibold text-slate-700 mb-2">
                            Link Google Maps (Opsional)
                        </label>
                        <div class="relative">
                            <input type="url"
                                   id="maps_url"
                                   name="maps_url"
                                   x-model="form.maps_url"
                                   placeholder="https://maps.google.com/..."
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Memudahkan peserta menavigasi rute langsung dari aplikasi.</p>
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
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-md shadow-cyan-500/20 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5">
                        <span>Lanjut ke Biaya &amp; Kuota</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN 3 : BIAYA, KUOTA & KONTAK                               --}}
        {{-- ============================================================ --}}
        <div x-show="step === 3"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6"
             style="display: none;">

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-sm">

                {{-- Section Title --}}
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-sm">
                        03
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Tiket, Kapasitas &amp; Persyaratan</h2>
                        <p class="text-xs text-slate-400">Tentukan apakah kegiatan gratis atau berbayar, batas jumlah peserta, dan kontak narahubung.</p>
                    </div>
                </div>

                {{-- Fields Container --}}
                <div class="mt-6 space-y-6">

                    {{-- 1. Jenis Tiket (Gratis vs Berbayar) --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-3">
                            Jenis Akses &amp; Biaya Pendaftaran <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            {{-- Pilihan Gratis --}}
                            <div class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex items-start gap-3.5"
                                 :class="form.is_paid === '0' 
                                    ? 'border-emerald-500 bg-emerald-50/40 shadow-sm' 
                                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'"
                                 @click="form.is_paid = '0'; form.registration_fee = '0'; formattedFee = ''">
                                <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center transition-colors"
                                     :class="form.is_paid === '0' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300'">
                                    <div x-show="form.is_paid === '0'" class="w-2 h-2 rounded-full bg-white"></div>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Gratis (Free Entry)</span>
                                    <span class="block text-xs text-slate-400 mt-0.5">Peserta dapat mendaftar tanpa dipungut biaya apa pun.</span>
                                </div>
                            </div>

                            {{-- Pilihan Berbayar --}}
                            <div class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex items-start gap-3.5"
                                 :class="form.is_paid === '1' 
                                    ? 'border-primary-500 bg-primary-50/40 shadow-sm' 
                                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'"
                                 @click="form.is_paid = '1'">
                                <div class="w-5 h-5 rounded-full border-2 mt-0.5 flex items-center justify-center transition-colors"
                                     :class="form.is_paid === '1' ? 'border-primary-600 bg-primary-600 text-white' : 'border-slate-300'">
                                    <div x-show="form.is_paid === '1'" class="w-2 h-2 rounded-full bg-white"></div>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="block text-sm font-bold text-slate-800">Berbayar (Tiket Berbayar)</span>
                                        @if(!$community->isVerified())
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">Perlu Verifikasi</span>
                                        @endif
                                    </div>
                                    <span class="block text-xs text-slate-400 mt-0.5">Dikenakan tiket untuk biaya sewa venue, konsumsi, atau hadiah.</span>
                                </div>
                            </div>

                        </div>
                        <input type="hidden" name="is_paid" :value="form.is_paid">
                    </div>

                    {{-- Warning jika Komunitas Belum Terverifikasi dan memilih berbayar --}}
                    @if(!$community->isVerified())
                        <div x-show="form.is_paid === '1'" x-transition class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                Perhatian: Komunitas Anda belum terverifikasi
                            </div>
                            <p>Hanya komunitas terverifikasi yang dapat mempublikasikan event berbayar. Anda dapat menyimpan event ini sebagai <strong>Draft</strong> sekarang, dan mempublikasikannya setelah komunitas Anda terverifikasi di menu Pengaturan Akun.</p>
                        </div>
                    @endif

                    {{-- Input Nominal (Jika Berbayar) --}}
                    <div x-show="form.is_paid === '1'" x-transition class="space-y-4">
                        <div>
                            <label for="registration_fee" class="block text-sm font-semibold text-slate-700 mb-2">
                                Biaya Pendaftaran per Peserta (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-sm font-bold text-slate-400 pointer-events-none">Rp</span>
                                <input type="text"
                                       id="registration_fee_display"
                                       :value="formattedFee"
                                       @input="handleFeeInput($event)"
                                       placeholder="Contoh : 50.000"
                                       class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm font-bold placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all duration-200"
                                       :class="errors.registration_fee ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <input type="hidden" name="registration_fee" :value="form.registration_fee">
                            </div>
                            <p x-show="errors.registration_fee" x-text="errors.registration_fee" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>

                        {{-- Metode & Rekening Pembayaran --}}
                        <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/90 space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                <h3 class="text-sm font-bold text-slate-800">Instruksi & Rekening Pembayaran</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="payment_method" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Metode / Nama Bank <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="payment_method"
                                           name="payment_method"
                                           x-model="form.payment_method"
                                           placeholder="Contoh : Transfer BCA / Mandiri / GoPay"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-primary-500">
                                </div>
                                <div>
                                    <label for="payment_account" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Nomor Rekening / No. HP <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="payment_account"
                                           name="payment_account"
                                           x-model="form.payment_account"
                                           placeholder="1234567890 a/n Nama Penyelenggara"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Batasan Kuota Peserta --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kapasitas / Kuota Peserta <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <label class="p-3.5 rounded-xl border flex items-center gap-3 cursor-pointer transition-all"
                                   :class="form.capacity_type === 'limited' ? 'border-primary-500 bg-primary-50/40 text-primary-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="capacity_type" value="limited" x-model="form.capacity_type" class="text-primary-600">
                                <span class="text-xs">Terbatas (Maksimal Kuota)</span>
                            </label>
                            <label class="p-3.5 rounded-xl border flex items-center gap-3 cursor-pointer transition-all"
                                   :class="form.capacity_type === 'unlimited' ? 'border-primary-500 bg-primary-50/40 text-primary-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="capacity_type" value="unlimited" x-model="form.capacity_type" class="text-primary-600">
                                <span class="text-xs">Tidak Terbatas (Unlimited)</span>
                            </label>
                        </div>

                        <div x-show="form.capacity_type === 'limited'" x-transition>
                            <div class="relative max-w-xs">
                                <input type="number"
                                       id="capacity"
                                       name="capacity"
                                       x-model="form.capacity"
                                       min="1"
                                       placeholder="Contoh : 30"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500"
                                       :class="errors.capacity ? 'border-red-400 ring-2 ring-red-100' : ''">
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs text-slate-400 font-semibold pointer-events-none">Orang</span>
                            </div>
                            @if($isEdit && $event->activeParticipantsCount() > 0)
                                <p class="text-[11px] text-amber-600 mt-1 font-medium">
                                    Catatan: Minimal {{ $event->activeParticipantsCount() }} kursi (jumlah peserta yang sudah mendaftar).
                                </p>
                            @endif
                            <p x-show="errors.capacity" x-text="errors.capacity" class="text-xs text-red-500 mt-1 font-medium"></p>
                        </div>
                    </div>

                    {{-- 3. Persyaratan Peserta --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="requirements" class="block text-sm font-semibold text-slate-700">
                                Syarat &amp; Ketentuan Peserta (Opsional)
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
                                  rows="3"
                                  x-model="form.requirements"
                                  placeholder="Contoh : Wajib memakai sepatu olahraga, membawa botol minum sendiri, dll."
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm font-mono placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500"></textarea>
                    </div>

                    {{-- 4. Narahubung / Kontak Penyelenggara --}}
                    <div>
                        <label for="organizer_contact" class="block text-sm font-semibold text-slate-700 mb-2">
                            Kontak Penyelenggara / Narahubung <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="organizer_contact"
                               name="organizer_contact"
                               x-model="form.organizer_contact"
                               placeholder="WhatsApp 0812-3456-7890 (Admin Budi) / Instagram @komunitas"
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/40 text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-primary-500"
                               :class="errors.organizer_contact ? 'border-red-400 ring-2 ring-red-100' : ''"
                               required>
                        <p x-show="errors.organizer_contact" x-text="errors.organizer_contact" class="text-xs text-red-500 mt-1 font-medium"></p>
                    </div>

                </div>

                {{-- Action / Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <button type="button"
                            @click="goToStep(2)"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors inline-flex items-center gap-2 self-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Sebelumnya
                    </button>
                    
                    <div class="flex items-center gap-3">
                        @if(!$isEdit || $event->status === 'draft')
                            <button type="button"
                                    @click="submitWithAction('draft')"
                                    :disabled="isSubmitting"
                                    class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold shadow-sm inline-flex items-center gap-2 transition-all disabled:opacity-50">
                                <span>Simpan Draft</span>
                            </button>
                        @endif

                        <button type="button"
                                @click="submitWithAction('publish')"
                                :disabled="isSubmitting"
                                class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/25 inline-flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5 disabled:opacity-50">
                            <span x-show="!isSubmitting">
                                {{ $isEdit ? ($event->status === 'draft' ? 'Publikasikan Event' : 'Simpan Perubahan') : 'Publikasikan Event Sekarang' }}
                            </span>
                            <span x-show="isSubmitting" class="inline-flex items-center gap-2" style="display: none;">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
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
        isEdit: @json($isEdit),
        isDragging: false,
        previewUrl: @json($initialPreviewUrl),
        errors: {},
        formattedFee: '',
        submitAction: 'publish',
        isSubmitting: false,
        eventTypes: [
            { 
                value: 'fun_match', 
                label: 'Fun Match',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
            },
            { 
                value: 'tournament', 
                label: 'Turnamen',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>'
            },
            { 
                value: 'gathering', 
                label: 'Gathering / Kumpul',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
            },
            { 
                value: 'workshop', 
                label: 'Workshop / Coaching',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>'
            },
            { 
                value: 'other', 
                label: 'Lainnya',
                icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" /></svg>'
            }
        ],
        form: @json($initialForm),
        init() {
            if (this.form.registration_fee && Number(this.form.registration_fee) > 0) {
                this.formattedFee = Number(this.form.registration_fee).toLocaleString('id-ID');
            }
        },
        setPaymentCategory(value) {
            this.form.payment_category = value;
            // Reset related fields
            this.form.payment_method = '';
            this.form.payment_provider = '';
            this.form.payment_account = '';
            this.form.qris_image = null;
            this.qrisPreviewUrl = null;
            this.errors.payment_account = null;
            this.errors.qris_image = null;
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
                } else if (this.form.title.trim().length > 80) {
                    this.errors.title = 'Nama event tidak boleh lebih dari 80 karakter.';
                }
                if (!this.form.type) {
                    this.errors.type = 'Pilih salah satu jenis event.';
                }
                if (!this.form.description || !this.form.description.trim()) {
                    this.errors.description = 'Deskripsi event wajib diisi.';
                }
                if (!this.isEdit && !this.previewUrl && !this.form.cover_image) {
                    this.errors.cover_image = 'Cover banner event wajib diunggah.';
                }

                if (Object.keys(this.errors).length === 0) {
                    this.goToStep(2);
                } else {
                    this.scrollToFirstError();
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
                    this.scrollToFirstError();
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
        handleQrisFileSelect(event) {
            const file = event.target.files[0];
            this.processQrisFile(file);
        },
        handleQrisDrop(event) {
            this.isQrisDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.$refs.qrisFileInput.files = event.dataTransfer.files;
                this.processQrisFile(file);
            }
        },
        processQrisFile(file) {
            if (!file) return;
            if (!file.type.match('image.*')) {
                this.errors.qris_image = 'File harus berupa gambar (JPG, PNG, atau WEBP).';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                this.errors.qris_image = 'Ukuran barcode QRIS maksimal 2MB.';
                return;
            }
            this.errors.qris_image = null;
            this.form.qris_image = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.qrisPreviewUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        removeQrisImage() {
            this.qrisPreviewUrl = null;
            this.form.qris_image = null;
            if (this.$refs.qrisFileInput) {
                this.$refs.qrisFileInput.value = '';
            }
        },
        insertRundownTemplate() {
            this.form.rundown = "06.00 - 06.30 : Registrasi Ulang & Pemanasan\n06.30 - 08.00 : Sesi Olahraga / Pertandingan\n08.00 - 08.30 : Cooling Down & Sesi Foto Bersama\n08.30 - 09.00 : Ramah Tamah & Penutupan";
        },
        insertRequirementsTemplate() {
            this.form.requirements = "1. Terbuka untuk umum / member komunitas\n2. Wajib mengenakan pakaian dan sepatu olahraga yang sesuai\n3. Membawa botol minum pribadi (tumbler)\n4. Kondisi tubuh dalam keadaan sehat dan fit\n5. Hadir di lokasi minimal 15 menit sebelum kegiatan dimulai";
        },
        submitWithAction(actionName) {
            this.submitAction = actionName;

            if (actionName === 'draft') {
                this.errors = {};
                if (!this.form.title || !this.form.title.trim()) {
                    this.errors.title = 'Nama event wajib diisi untuk menyimpan draft.';
                    this.goToStep(1);
                    this.scrollToFirstError();
                    return;
                } else if (this.form.title.trim().length > 80) {
                    this.errors.title = 'Nama event tidak boleh lebih dari 80 karakter.';
                    this.goToStep(1);
                    this.scrollToFirstError();
                    return;
                }
                this.isSubmitting = true;
                const formElem = document.getElementById('eventForm');
                this.$nextTick(() => formElem.submit());
                return;
            }

            // Publish action validates everything
            const formElem = document.getElementById('eventForm');
            formElem.requestSubmit();
        },
        handleSubmit(event) {
            if (this.submitAction === 'draft') {
                return true;
            }

            this.errors = {};

            // Validasi Step 1
            if (!this.form.title || !this.form.title.trim()) {
                this.errors.title = 'Nama event wajib diisi.';
            } else if (this.form.title.trim().length > 80) {
                this.errors.title = 'Nama event tidak boleh lebih dari 80 karakter.';
            }
            if (!this.form.type) {
                this.errors.type = 'Pilih salah satu jenis event.';
            }
            if (!this.form.description || !this.form.description.trim()) {
                this.errors.description = 'Deskripsi event wajib diisi.';
            }
            if (!this.isEdit && !this.previewUrl && !this.form.cover_image) {
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
                if (this.form.payment_method === 'QRIS') {
                    if (!this.qrisPreviewUrl && !this.form.qris_image) {
                        this.errors.qris_image = 'Foto barcode QRIS wajib diunggah.';
                    }
                } else {
                    if (!this.form.payment_account || !this.form.payment_account.trim()) {
                        this.errors.payment_account = 'Nomor rekening / tujuan pembayaran wajib diisi.';
                    }
                }
            }
            if (!this.form.organizer_contact || !this.form.organizer_contact.trim()) {
                this.errors.organizer_contact = 'Kontak narahubung panitia wajib diisi.';
            }

            if (Object.keys(this.errors).length > 0) {
                event.preventDefault();
                if (this.errors.title || this.errors.type || this.errors.description || this.errors.cover_image) {
                    this.goToStep(1);
                } else if (this.errors.event_date || this.errors.registration_close_at || this.errors.start_time || this.errors.location) {
                    this.goToStep(2);
                } else {
                    this.goToStep(3);
                }
                this.scrollToFirstError();
                return false;
            }

            this.isSubmitting = true;
            return true;
        },
        scrollToFirstError() {
            this.$nextTick(() => {
                const firstError = document.querySelector('[class*="border-red-400"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    };
}
</script>
@endpush
