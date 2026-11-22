@php
    $authUser = auth()->user();
    $userInitials = collect(explode(' ', $authUser?->name ?? 'U'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');

    // Ambil komunitas milik user login saat ini (jika ada)
    $ownedCommunity = $authUser ? \App\Models\Community::where('owner_id', $authUser->id)->first() : null;

    // Ambil laporan peringatan resmi admin terhadap komunitas ini dari action_note
    $warningReports = $ownedCommunity ? \App\Models\Report::where('reportable_type', \App\Models\Community::class)
        ->where('reportable_id', $ownedCommunity->id)
        ->whereNotNull('action_note')
        ->where('action_note', 'like', '%Peringatan Resmi%')
        ->latest('updated_at')
        ->get() : collect();

    // Ambil notifikasi dari tabel notifications
    $userNotifications = $authUser ? \App\Models\UserAnnouncement::where('user_id', $authUser->id)
        ->latest()
        ->take(5)
        ->get() : collect();

    // Gabungkan notifikasi agar selalu tampil meskipun peringatan dikirim sebelum kode UserAnnouncement dipasang
    $notifications = collect();

    foreach ($warningReports as $wReport) {
        // Ambil potongan teks peringatan resmi dari action_note
        $actionLines = explode("\n\n", $wReport->action_note);
        $warningLine = collect($actionLines)->filter(fn($l) => str_contains($l, 'Peringatan Resmi'))->last() ?? $wReport->action_note;

        $notifications->push((object)[
            'id' => 'report-' . $wReport->id,
            'title' => 'Peringatan resmi moderasi: ' . $ownedCommunity->name,
            'body' => preg_replace('/^\[.*?Peringatan Resmi\]:\s*/', '', $warningLine),
            'read_at' => null,
            'created_at' => $wReport->updated_at ?? $wReport->created_at,
            'is_warning' => true,
        ]);
    }

    foreach ($userNotifications as $uNotif) {
        $notifications->push((object)[
            'id' => 'notif-' . $uNotif->id,
            'title' => $uNotif->title,
            'body' => $uNotif->body,
            'read_at' => $uNotif->read_at,
            'created_at' => $uNotif->created_at,
            'is_warning' => false,
        ]);
    }

    $hasUnread = $notifications->whereNull('read_at')->isNotEmpty();
@endphp

<header class="admin-header">
    <div class="admin-header__left flex items-center gap-3">
        {{-- Mobile Hamburger Toggle Button --}}
        <button type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 inline-flex items-center justify-center"
                aria-label="Buka Menu Navigasi">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div class="admin-header__actions">
        {{-- Notification Bell & Dropdown --}}
        <div class="relative" x-data="{ openNotif: false, unread: {{ $hasUnread ? 'true' : 'false' }} }">
            <button @click="openNotif = !openNotif"
                    type="button"
                    class="admin-header__notification relative cursor-pointer"
                    aria-label="Notifikasi">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span x-show="unread" class="admin-header__notification-dot"></span>
            </button>

            {{-- Dropdown Menu Notifikasi --}}
            <div x-show="openNotif"
                 @click.away="openNotif = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 z-50 text-xs space-y-3"
                 style="display: none;">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">Notifikasi</span>
                        @if($hasUnread)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">Baru</span>
                        @endif
                    </div>
                    <button type="button" @click="unread = false" class="text-[11px] text-primary-600 hover:text-primary-700 font-semibold cursor-pointer">
                        Tutup
                    </button>
                </div>

                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($notifications as $notif)
                        <div class="p-3 rounded-xl border {{ is_null($notif->read_at) ? 'bg-amber-50/60 border-amber-200/70' : 'bg-slate-50 border-slate-100' }} text-slate-700 space-y-1">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-bold text-slate-900 text-xs leading-snug">{{ $notif->title }}</span>
                                <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">{{ $notif->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $notif->body }}</p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            Belum ada notifikasi masuk.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tombol Buat Event --}}
        <a href="{{ route('penyedia-komunitas.events.create') }}" class="btn btn--primary btn--sm inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Event</span>
        </a>

        {{-- Avatar Profil (Mengarah ke Halaman Akun) --}}
        <a href="{{ route('penyedia-komunitas.akun.index') }}"
           class="admin-header__profile block hover:opacity-90 transition-opacity"
           title="Pengaturan Akun ({{ $authUser?->name }})"
           aria-label="Pengaturan Akun">
            @if($authUser?->avatar)
                <img src="{{ \Illuminate\Support\Str::startsWith($authUser->avatar, ['http://', 'https://']) ? $authUser->avatar : \Illuminate\Support\Facades\Storage::url($authUser->avatar) }}" alt="{{ $authUser->name }}"
                    class="admin-header__avatar"
                    onerror="this.style.display='none'">
            @else
                <span class="flex items-center justify-center w-full h-full
                                 bg-gradient-to-br from-primary-500 to-primary-700
                                 text-white text-xs font-bold rounded-full">
                    {{ $userInitials }}
                </span>
            @endif
        </a>
    </div>
</header>