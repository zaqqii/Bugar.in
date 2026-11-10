@extends('layouts.penyedia-komunitas')

@section('title', 'Detail Jadwal: ' . $schedule->title . ' - Penyedia Komunitas')

@section('content')
<div class="max-w-5xl mx-auto pb-16 space-y-6" x-data="{
    blockModalOpen: false,
    blockActionUrl: '',
    blockUserName: '',
    confirmBlock(url, name) {
        this.blockActionUrl = url;
        this.blockUserName = name;
        this.blockModalOpen = true;
    }
}">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('penyedia-komunitas.jadwal.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Jadwal
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Tombol Buka Check-in QR --}}
            @php
                $tz = config('app.timezone', 'Asia/Jakarta');
                $isScheduleToday = $schedule->date && \Carbon\Carbon::parse($schedule->date, $tz)->isToday();
            @endphp
            @if($isScheduleToday && !$schedule->isCancelled())
                <a href="{{ route('penyedia-komunitas.jadwal.checkin', $schedule) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                    <span>📷 Buka Scanner Check-in</span>
                </a>
            @else
                <button type="button"
                        disabled
                        title="{{ $schedule->isCancelled() ? 'Sesi dibatalkan' : ($schedule->isFinished() ? 'Sesi telah selesai' : 'Check-in hanya aktif pada hari sesi berlangsung') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                    <span>📷 Check-in (Nonaktif)</span>
                </button>
            @endif

            @if(!$schedule->isCancelled() && !$schedule->isFinished())
                <a href="{{ route('penyedia-komunitas.jadwal.edit', $schedule) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    <span>Edit Jadwal</span>
                </a>

                {{-- Batalkan Sesi --}}
                <form method="POST" action="{{ route('penyedia-komunitas.jadwal.cancel', $schedule) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sesi ini? Seluruh peserta terdaftar akan dibatalkan.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition">
                        <span>Batalkan Sesi</span>
                    </button>
                </form>

                {{-- Batalkan Seri --}}
                @if($schedule->series_id)
                    <form method="POST" action="{{ route('penyedia-komunitas.jadwal.cancel-series', $schedule) }}" onsubmit="return confirm('Batalkan seluruh sesi mendatang dalam seri ini?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-amber-300 bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold transition">
                            <span>Batalkan Seluruh Seri</span>
                        </button>
                    </form>
                @endif
            @endif
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

    {{-- Jadwal Overview Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider">
                        {{ $community->sport_category ?? 'Olahraga' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $schedule->levelBadgeClass() }}">
                        {{ $schedule->levelLabel() }}
                    </span>
                    @if($schedule->series_id)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold">
                            🔗 Bagian Dari Seri
                        </span>
                    @endif
                    @if($schedule->streak_enabled)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
                            🔥 Streak Aktif
                        </span>
                    @endif
                    @if($schedule->isCancelled())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                            Dibatalkan
                        </span>
                    @elseif($schedule->isFinished())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                            Selesai
                        </span>
                    @elseif($schedule->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                            Nonaktif
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight break-words">
                    {{ $schedule->title }}
                </h1>

                @if($schedule->description)
                    <p class="text-slate-600 text-sm whitespace-pre-line leading-relaxed max-w-3xl">
                        {{ $schedule->description }}
                    </p>
                @endif
            </div>

            {{-- Kuota Badge Box --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 min-w-[200px] shrink-0 text-right sm:text-left">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kapasitas Terisi</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-extrabold text-slate-900">{{ $activeParticipantsCount }}</span>
                    <span class="text-xs text-slate-500 font-semibold">/ {{ $schedule->capacity ? $schedule->capacity . ' Peserta' : '∞ (Tanpa Batas)' }}</span>
                </div>
                @if($schedule->capacity)
                    @php
                        $fillRate = min(100, round(($activeParticipantsCount / $schedule->capacity) * 100));
                    @endphp
                    <div class="w-full h-1.5 bg-slate-200 rounded-full mt-2 overflow-hidden">
                        <div class="h-full rounded-full {{ $fillRate >= 100 ? 'bg-rose-500' : ($fillRate >= 80 ? 'bg-amber-500' : 'bg-blue-600') }}"
                             style="width: {{ $fillRate }}%"></div>
                    </div>
                    <span class="block text-[10px] text-slate-400 mt-1">{{ $fillRate }}% Terisi (Sisa: {{ $schedule->remainingSlots() }})</span>
                @else
                    <span class="block text-[10px] text-emerald-600 font-bold mt-1">Pendaftaran Tanpa Batas</span>
                @endif
            </div>
        </div>

        {{-- Meta Info Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-6 border-t border-slate-100 text-xs">
            {{-- Waktu & Tanggal --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                    📅
                </div>
                <div>
                    <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Tanggal & Waktu</span>
                    <span class="font-extrabold text-slate-800 text-sm block">
                        {{ $schedule->date ? $schedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : $schedule->dayLabel() }}
                    </span>
                    <span class="text-slate-500 block">
                        {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB
                    </span>
                </div>
            </div>

            {{-- Lokasi --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Tempat Latihan</span>
                    <span class="font-extrabold text-slate-800 text-sm break-words block" title="{{ $schedule->location }}">
                        {{ $schedule->location }}
                    </span>
                    @if($schedule->maps_url)
                        <a href="{{ $schedule->maps_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 font-semibold hover:underline inline-flex items-center gap-1 mt-0.5">
                            Buka di Google Maps &nearr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- Penyelenggara --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <div>
                    <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Komunitas Penyelenggara</span>
                    <span class="font-extrabold text-slate-800 text-sm block">{{ $community->name }}</span>
                    <span class="text-slate-500 block">{{ $community->city ?? 'Indonesia' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Peserta Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Daftar Peserta Terdaftar</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pengguna yang telah mendaftar ke sesi latihan bertanggal ini.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold self-start sm:self-auto">
                Total: {{ $participants->total() }} Peserta
            </span>
        </div>

        @if($participants->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 014.5 0z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Belum Ada Peserta</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto mt-1">
                    Peserta yang mendaftar ke sesi ini akan muncul secara otomatis di tabel ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                            <th class="py-3 px-5">No</th>
                            <th class="py-3 px-5">Peserta</th>
                            <th class="py-3 px-5">Status Sesi</th>
                            <th class="py-3 px-5">Riwayat Kehadiran Komunitas</th>
                            <th class="py-3 px-5">Tanggal Mendaftar</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($participants as $index => $participant)
                            @php
                                $uStats = $attendanceStats[$participant->user_id] ?? null;
                                $attended = $uStats->attended_count ?? 0;
                                $totalEval = $uStats->total_evaluated ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-5 font-semibold text-slate-400">
                                    {{ $participants->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($participant->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-slate-900 truncate">
                                                {{ $participant->user->name ?? 'Pengguna #' . $participant->user_id }}
                                            </span>
                                            <span class="block text-[11px] text-slate-400 truncate">
                                                {{ $participant->user->email ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    @php
                                        $colorMap = [
                                            'registered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'cancelled'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'attended'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'no_show'    => 'bg-amber-50 text-amber-700 border-amber-200',
                                        ];
                                        $badgeClass = $colorMap[$participant->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                        {{ $participant->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $totalEval > 0 && ($attended / $totalEval) < 0.5 ? 'text-amber-700' : 'text-slate-700' }}">
                                        <span>hadir {{ $attended }} dari {{ $totalEval }} sesi</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-slate-500">
                                    {{ $participant->registered_at ? $participant->registered_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Tombol Keluarkan Peserta --}}
                                        @if($participant->status === 'registered')
                                            <form method="POST" action="{{ route('penyedia-komunitas.jadwal.participants.remove', [$schedule, $participant]) }}" onsubmit="return confirm('Keluarkan peserta {{ addslashes($participant->user->name ?? 'ini') }} dari jadwal?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Keluarkan dari sesi ini"
                                                        class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-[11px] transition">
                                                    Keluarkan
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Tombol Blokir dari Komunitas --}}
                                        <button type="button"
                                                @click="confirmBlock('{{ route('penyedia-komunitas.jadwal.participants.block', [$schedule, $participant]) }}', '{{ addslashes($participant->user->name ?? 'Pengguna') }}')"
                                                title="Blokir dari komunitas ini"
                                                class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-[11px] transition">
                                            Blokir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $participants->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Modal Konfirmasi Blokir --}}
    <div x-show="blockModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4"
             @click.away="blockModalOpen = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl">
                🛡️
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Blokir Pengguna dari Komunitas?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Pengguna <strong x-text="blockUserName"></strong> tidak akan bisa mendaftar ke jadwal manapun di komunitas ini. Seluruh jadwal mendatang yang telah ia daftarkan di komunitas ini akan otomatis dibatalkan.
                </p>
            </div>
            <form :action="blockActionUrl" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="block_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan Pemblokiran (Opsional)</label>
                    <input type="text"
                           id="block_reason"
                           name="reason"
                           placeholder="Contoh: Sering tidak hadir (no-show), melanggar aturan"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="blockModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md">
                        Ya, Blokir Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
