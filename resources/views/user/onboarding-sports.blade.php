<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F0F7FF]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pilih Cabang Olahraga — Bugar.in</title>
    <meta name="description" content="Pilih cabang olahraga favoritmu untuk personalisasi komunitas dan event olahraga di Bugar.in.">

    <!-- Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            200: '#BFDBFE',
                            300: '#93C5FD',
                            400: '#60A5FA',
                            500: '#3B82F6',
                            600: '#2563EB',
                            700: '#1D4ED8',
                            800: '#1E40AF',
                            900: '#1E3A8A',
                        },
                        cyan: {
                            50: '#ECFEFF',
                            100: '#CFFAFE',
                            200: '#A5F3FC',
                            300: '#67E8F9',
                            400: '#22D3EE',
                            500: '#06B6D4',
                            600: '#0891B2',
                            700: '#0E7490',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between"
      x-data="onboardingApp()"
      x-cloak>

    <!-- Top Navigation Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-extrabold text-base text-slate-900 tracking-tight block leading-tight">Bugar.in</span>
                    <span class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase block">Personalisasi Akun</span>
                </div>
            </a>

            <!-- Step Progress Indicator -->
            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    1. Akun Dibuat
                </span>
                <span class="text-slate-300">→</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-bold">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    2. Pilih Cabor Minat
                </span>
                <span class="text-slate-300">→</span>
                <span class="text-slate-400">3. Siap Olahraga</span>
            </div>

            <!-- Skip / Direct to Dashboard -->
            <a href="{{ route('user.dashboard') }}"
               class="text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors inline-flex items-center gap-1">
                <span>Lewati Dulu</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

        <!-- Flash Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 font-bold">
                    ✓
                </div>
                <div>
                    <span class="font-bold block text-emerald-900">Pendaftaran Akun Berhasil!</span>
                    <span class="text-xs text-emerald-700">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Form Wrapper -->
        <form method="POST" action="{{ route('user.onboarding.sports.store') }}" @submit="isSubmitting = true">
            @csrf

            <!-- Hero Welcome Card -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs mb-8 text-center relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-blue-100/50 blur-2xl pointer-events-none"></div>
                <div class="absolute -left-16 -bottom-16 w-56 h-56 rounded-full bg-cyan-100/50 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold uppercase tracking-wider mb-3">
                        ⚡ Personalisasi Olahraga Anda
                    </span>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-3">
                        Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-600">{{ explode(' ', $user->name)[0] }}</span>! <br class="hidden sm:inline">Pilih Cabang Olahraga Favoritmu
                    </h1>
                    <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                        Pilih satu atau lebih olahraga yang kamu sukai. Kami akan mengutamakan rekomendasi komunitas lokal, jadwal latihan bareng, dan event yang cocok untukmu.
                    </p>
                </div>
            </div>

            <!-- Sports Selection Area Card -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs mb-8">
                
                <!-- Search & Category Filters Bar -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 mb-6 border-b border-slate-100">
                    
                    <!-- Search Input -->
                    <div class="relative max-w-sm w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari cabor (cth. Padel, Badminton, Lari)..."
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none">
                    </div>

                    <!-- Category Filter Buttons -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full no-scrollbar">
                        <template x-for="cat in categories" :key="cat">
                            <button type="button"
                                    @click="selectedCategory = cat"
                                    :class="selectedCategory === cat
                                        ? 'bg-blue-600 text-white font-bold shadow-xs'
                                        : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold'"
                                    class="px-3.5 py-1.5 rounded-full text-xs transition-all whitespace-nowrap cursor-pointer">
                                <span x-text="cat"></span>
                            </button>
                        </template>
                    </div>

                </div>

                <!-- Selected Count Bar -->
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">PILIHAN CABOR:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-700"
                              x-text="`${selectedSports.length} dipilih`">
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button"
                                x-show="selectedSports.length > 0"
                                @click="selectedSports = []"
                                class="text-xs font-bold text-rose-500 hover:text-rose-700 transition-colors cursor-pointer">
                            Reset Pilihan
                        </button>
                    </div>
                </div>

                <!-- Sports Grid (Responsive 2 to 4 cols) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                    <template x-for="sport in filteredSports" :key="sport.id">
                        <div @click="toggleSport(sport.id)"
                             :class="isSportSelected(sport.id)
                                 ? 'bg-blue-50/90 border-blue-600 ring-2 ring-blue-500/20 shadow-sm'
                                 : 'bg-white border-slate-200 hover:border-blue-300 hover:bg-slate-50/70'"
                             class="rounded-2xl border p-4 cursor-pointer transition-all duration-150 select-none flex flex-col justify-between relative group hover:-translate-y-0.5">
                            
                            <!-- Top Row: Icon + Checkmark -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                    <span x-text="sport.icon"></span>
                                </div>

                                <!-- Custom Checkbox indicator -->
                                <div :class="isSportSelected(sport.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300 bg-white'"
                                     class="w-6 h-6 rounded-lg border flex items-center justify-center transition-colors">
                                    <svg x-show="isSportSelected(sport.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Sport Details -->
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5" x-text="sport.category"></span>
                                <h3 class="text-sm font-extrabold text-slate-900 leading-snug" x-text="sport.label"></h3>
                            </div>

                            <!-- Hidden inputs submitted with form -->
                            <template x-if="isSportSelected(sport.id)">
                                <input type="hidden" name="sports[]" :value="sport.id">
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Empty State if search finds nothing -->
                <div x-show="filteredSports.length === 0" class="text-center py-12">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                        🔍
                    </div>
                    <h4 class="text-sm font-bold text-slate-700">Tidak ada cabang olahraga yang cocok</h4>
                    <p class="text-xs text-slate-400 mt-1">Coba kata kunci pencarian lain atau pilih kategori Semua.</p>
                </div>

            </div>

            <!-- Optional Step: Level & Frekuensi Target -->
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs mb-8">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 text-cyan-600 flex items-center justify-center font-bold">
                        🎯
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Target &amp; Level Kebugaran (Opsional)</h3>
                        <p class="text-xs text-slate-500">Membantu kami mencocokkan sesi latihan dengan level yang sepadan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Fitness Level -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Level Pengalaman</label>
                        <select name="fitness_level"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none cursor-pointer">
                            <option value="beginner">Beginner / Pemula</option>
                            <option value="intermediate" selected>Intermediate / Aktif Rekreasional</option>
                            <option value="advanced">Advanced / Kompetitif</option>
                            <option value="elite">Elite / Semi-Pro</option>
                        </select>
                    </div>

                    <!-- Weekly Target -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Target Rutin Mingguan</label>
                        <select name="weekly_target"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none cursor-pointer">
                            <option value="1-2">1-2 Sesi per minggu (Santai)</option>
                            <option value="3-4" selected>3-4 Sesi per minggu (Disiplin)</option>
                            <option value="5-6">5-6 Sesi per minggu (Intensif)</option>
                            <option value="7">Setiap Hari (High Volume)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="sticky bottom-4 z-20 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200 shadow-lg p-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-slate-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Kamu selalu bisa mengubah pilihan cabang olahraga ini kapan saja di menu <strong class="text-slate-800">Edit Profil</strong>.</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <!-- Skip Button -->
                    <a href="{{ route('user.dashboard') }}"
                       class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                        Lewati Dulu
                    </a>

                    <!-- Submit Button -->
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-blue-500/25 transition-all cursor-pointer active:scale-95 disabled:opacity-60">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Masuk ke Dashboard'">Simpan &amp; Masuk ke Dashboard</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>

        </form>

    </main>

    <!-- Alpine.js Logic -->
    <script>
        function onboardingApp() {
            const rawSports = @json($availableSports);
            const initialSports = @json($userSports ?? []);

            // Normalisasi alias jika user sudah memiliki cabor dengan key alternatif
            const aliasMap = {
                'running': 'lari',
                'cycling': 'sepeda',
                'swimming': 'renang',
                'strength': 'gym',
                'fitness': 'gym',
                'basketball': 'basket',
                'trail': 'hiking',
                'outdoor': 'hiking',
                'martial': 'bela_diri',
                'martial_arts': 'bela_diri',
                'tennis': 'tenis',
                'table_tennis': 'tenis_meja',
                'football': 'sepakbola',
                'soccer': 'sepakbola',
                'volleyball': 'voli',
            };

            const normalizedSelected = initialSports.map(s => aliasMap[s] || s);

            return {
                isSubmitting: false,
                searchQuery: '',
                selectedCategory: 'Semua',
                categories: ['Semua', 'Cardio', 'Raket', 'Tim', 'Strength & Fitness', 'Outdoor & Combat', 'Akurasi & Lainnya'],
                sportsList: rawSports,
                selectedSports: normalizedSelected.length > 0 ? normalizedSelected : ['lari', 'badminton'],

                get filteredSports() {
                    return this.sportsList.filter(sport => {
                        const matchesCategory = this.selectedCategory === 'Semua' || sport.category === this.selectedCategory;
                        const query = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !query ||
                            sport.label.toLowerCase().includes(query) ||
                            sport.id.toLowerCase().includes(query) ||
                            (sport.aliases && sport.aliases.some(a => a.toLowerCase().includes(query)));
                        return matchesCategory && matchesSearch;
                    });
                },

                isSportSelected(id) {
                    return this.selectedSports.includes(id);
                },

                toggleSport(id) {
                    if (this.selectedSports.includes(id)) {
                        this.selectedSports = this.selectedSports.filter(s => s !== id);
                    } else {
                        this.selectedSports.push(id);
                    }
                }
            };
        }
    </script>
</body>
</html>

