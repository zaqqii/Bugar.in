<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 bg-slate-950/50">
                <th class="py-4 px-5">Nama Komunitas</th>
                <th class="py-4 px-5">Cabor & Kota</th>
                <th class="py-4 px-5">Pengelola (Owner)</th>
                <th class="py-4 px-5">Statistik</th>
                <th class="py-4 px-5">Status Verifikasi</th>
                <th class="py-4 px-5">Operasional</th>
                <th class="py-4 px-5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/70 text-sm">
            @forelse($communities as $comm)
                <tr class="hover:bg-slate-900/40 transition">
                    <td class="py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold text-xs uppercase">
                                {{ substr($comm->name, 0, 2) }}
                            </div>
                            <div>
                                <span class="font-bold text-white block">{{ $comm->name }}</span>
                                <span class="text-xs text-slate-400">/{{ $comm->slug ?? Str::slug($comm->name) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-5">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-semibold mb-1">
                            {{ ucfirst($comm->sport_category) }}
                        </span>
                        <div class="text-xs text-slate-400">{{ $comm->city ?? 'Malang' }}</div>
                    </td>
                    <td class="py-4 px-5">
                        <span class="font-medium text-slate-200 block text-xs">{{ $comm->owner->name ?? 'Admin Host' }}</span>
                        <span class="text-[11px] text-slate-500">{{ $comm->owner->email ?? '-' }}</span>
                    </td>
                    <td class="py-4 px-5 text-xs text-slate-300">
                        <div>Anggota: <span class="font-bold text-white">{{ $comm->members_count ?? 0 }}</span></div>
                        <div>Pengikut: <span class="font-bold text-white">1</span></div>
                    </td>
                    <td class="py-4 px-5">
                        @if($comm->verification_status === 'verified')
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold">Verified</span>
                        @elseif($comm->verification_status === 'pending')
                            <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-semibold">Pending</span>
                        @elseif($comm->verification_status === 'suspended')
                            <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-semibold">Suspended</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-xs font-semibold">Rejected</span>
                        @endif
                    </td>
                    <td class="py-4 px-5">
                        @if($comm->is_active)
                            <span class="px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-semibold">Aktif</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-xs font-semibold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-right">
                        <a href="{{ route('admin.communities.show', $comm->id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition">
                            Kelola Detail &rarr;
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-500 text-xs">
                        Tidak ada data komunitas yang ditemukan dengan filter tersebut.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginasi --}}
@if($communities->hasPages())
    <div class="px-5 py-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <div>
            Menampilkan <span class="font-semibold text-white">{{ $communities->firstItem() ?? 0 }}</span>
            – <span class="font-semibold text-white">{{ $communities->lastItem() ?? 0 }}</span>
            dari <span class="font-semibold text-white">{{ $communities->total() }}</span> komunitas
        </div>
        <div>
            {{ $communities->links() }}
        </div>
    </div>
@endif