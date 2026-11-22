@extends('layouts.admin')

@section('title', 'Verifikasi Komunitas — Console Ops')

@section('content')
<div x-data="{
        rejectModalOpen: false,
        rejectActionUrl: '',
        rejectCommunityName: '',
        rejectionReason: '',
        confirmReject: false,
        openReject(url, name) {
            this.rejectActionUrl = url;
            this.rejectCommunityName = name;
            this.rejectionReason = '';
            this.confirmReject = false;
            this.rejectModalOpen = true;
        }
    }"
    class="min-h-screen bg-[#0B1120] text-slate-100 p-6 lg:p-8 space-y-6 font-sans">

    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#111827] via-[#131C31] to-[#0F172A] border border-slate-800/90 p-6 lg:p-7 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/70 text-xs font-medium text-amber-400 mb-3">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                Antrean Verifikasi Komunitas
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-white">
                Verifikasi Komunitas
            </h1>
            <p class="text-sm text-slate-400 mt-1 max-w-2xl">
                Memuat komunitas berstatus pending dengan waktu pengajuan terisi, diurutkan dari pengajuan terlama.
            </p>
        </div>

        <div class="px-4 py-3 rounded-xl bg-slate-950/70 border border-slate-800 shrink-0">
            <div class="text-[11px] uppercase tracking-wider text-amber-400 font-semibold">Total Antrean</div>
            <div class="text-xl font-bold text-white mt-0.5">{{ $verifications->total() }}</div>
        </div>
    </div>

    {{-- Flash & Validation Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @error('rejection_reason')
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- Tabel Antrean --}}
    <div class="rounded-2xl bg-[#111827] border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3.5">Komunitas</th>
                        <th class="px-5 py-3.5">Tipe</th>
                        <th class="px-5 py-3.5">Biaya</th>
                        <th class="px-5 py-3.5">Metode Pembayaran</th>
                        <th class="px-5 py-3.5">Waktu Pengajuan</th>
                        <th class="px-5 py-3.5">Jumlah Pengikut</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/70 text-xs">
                    @forelse($verifications as $item)
                        <tr class="hover:bg-slate-900/50 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-white text-sm">{{ $item->name }}</div>
                                <div class="text-slate-400 mt-0.5">
                                    {{ $item->owner->name ?? '-' }} &bull; {{ ucfirst($item->sport_category ?? '-') }}
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono">
                                @if($item->verification_type === 'paid')
                                    <span class="px-2.5 py-1 rounded-md font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                        paid
                                    </span>
                                @elseif($item->verification_type === 'free_followers')
                                    <span class="px-2.5 py-1 rounded-md font-semibold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">
                                        free_followers
                                    </span>
                                @else
                                    <span class="text-slate-400">{{ $item->verification_type ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-200">
                                @if(!is_null($item->verification_fee))
                                    Rp {{ number_format((float) $item->verification_fee, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-300">
                                {{ $item->verification_payment_method ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                {{ $item->verification_requested_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-white">
                                {{ number_format((int) $item->followers_count) }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.verifications.approve', $item) }}"
                                          onsubmit="return confirm('Konfirmasi: Setujui permohonan verifikasi untuk komunitas \'{{ addslashes($item->name) }}\'?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition cursor-pointer">
                                            Approve
                                        </button>
                                    </form>

                                    <button type="button"
                                        @click="openReject('{{ route('admin.verifications.reject', $item) }}', '{{ addslashes($item->name) }}')"
                                        class="px-3 py-1.5 rounded-lg font-semibold bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 transition cursor-pointer">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                Belum ada komunitas berstatus pending dengan waktu pengajuan terisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-800 bg-slate-950/40">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Reject (Wajib Alasan >= 10 Karakter & Konfirmasi) --}}
    <div x-show="rejectModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4">
        <div @click.away="rejectModalOpen = false"
             class="w-full max-w-lg rounded-2xl bg-[#111827] border border-slate-700 p-6 shadow-2xl space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-white">Tolak Verifikasi Komunitas</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Komunitas: ' + rejectCommunityName"></p>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
            </div>

            <form method="POST" :action="rejectActionUrl" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="rejection_reason" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Alasan Penolakan (Wajib Minimal 10 Karakter) <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" minlength="10" required
                        x-model="rejectionReason"
                        placeholder="Tuliskan alasan penolakan yang jelas agar tampil ke penyedia komunitas..."
                        class="w-full rounded-xl bg-slate-950 border border-slate-700 text-sm text-slate-100 placeholder-slate-500 p-3 focus:outline-none focus:border-rose-500"></textarea>
                    <div class="flex justify-between text-[11px] mt-1">
                        <span class="text-slate-400">Akan ditampilkan ke penyedia komunitas.</span>
                        <span :class="rejectionReason.trim().length >= 10 ? 'text-emerald-400' : 'text-rose-400'"
                              x-text="rejectionReason.trim().length + '/10 karakter'"></span>
                    </div>
                </div>

                <label class="flex items-start gap-2.5 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" x-model="confirmReject" required
                        class="mt-0.5 rounded border-slate-600 bg-slate-900 text-rose-600 focus:ring-rose-500">
                    <span>Saya mengonfirmasi tindakan penolakan verifikasi ini.</span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="rejectModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        :disabled="rejectionReason.trim().length < 10 || !confirmReject"
                        :class="(rejectionReason.trim().length < 10 || !confirmReject) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-rose-500 cursor-pointer'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white transition">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection