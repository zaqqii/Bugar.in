<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommunityManagementController extends Controller
{
    /**
     * 1. Halaman daftar semua komunitas:
     * Cari (nama), filter (status verifikasi, aktif/nonaktif, cabang olahraga), paginasi.
     */
    public function index(Request $request)
    {
        $query = Community::query();

        // 1. Filter Pencarian Nama Komunitas
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 2. Filter Status Verifikasi
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // 3. Filter Status Operasional (is_active: 1 atau 0)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // 4. Filter Cabang Olahraga
        if ($request->filled('sport_category')) {
            $query->where('sport_category', $request->sport_category);
        }

        $communities = $query->latest()->paginate(10)->withQueryString();
        $sportCategories = Community::distinct()->pluck('sport_category');

        // Hitung ringkasan statistik untuk kartu di bagian atas header
        $stats = [
            'total'     => Community::count(),
            'verified'  => Community::where('verification_status', 'verified')->count(),
            'suspended' => Community::where('verification_status', 'suspended')->count(),
            'inactive'  => Community::where('is_active', false)->count(),
        ];

        // Jika request datang dari AJAX (filter / paginasi tabel), kembalikan partial table
        if ($request->ajax()) {
            return view('admin.communities.partials.table', compact('communities'));
        }

        return view('admin.communities.index', compact('communities', 'sportCategories', 'stats'));
    }

    /**
     * 2. Halaman detail komunitas:
     * Pemilik, jumlah pengikut & anggota, jumlah jadwal & event, status.
     */
    public function show(Community $community)
    {
        $community->load([
            'owner',
            'schedules' => fn ($q) => $q->latest('date')->limit(5),
            'events'    => fn ($q) => $q->latest('event_date')->limit(5),
        ])->loadCount(['schedules', 'events']);

        return view('admin.communities.show', compact('community'));
    }

    /**
     * 3a. Aksi: Aktifkan / Nonaktifkan komunitas
     */
    public function toggleActive(Request $request, Community $community)
    {
        $oldStatus = $community->is_active;
        $newStatus = ! $oldStatus;

        DB::transaction(function () use ($community, $oldStatus, $newStatus, $request) {
            $community->is_active = $newStatus;
            $community->save();

            // Catat ke audit_logs sesuai schema database.md
            DB::table('audit_logs')->insert([
                'user_id'        => Auth::id(),
                'action'         => $newStatus ? 'community.activated' : 'community.deactivated',
                'auditable_type' => 'community',
                'auditable_id'   => $community->id,
                'old_values'     => json_encode(['is_active' => $oldStatus]),
                'new_values'     => json_encode(['is_active' => $newStatus]),
                'ip_address'     => $request->ip(),
                'user_agent'     => substr((string) $request->userAgent(), 0, 255),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        $pesan = $newStatus
            ? "Komunitas \"{$community->name}\" berhasil diaktifkan kembali."
            : "Komunitas \"{$community->name}\" berhasil dinonaktifkan.";

        return back()->with('success', $pesan);
    }

    /**
     * 3b. Aksi: Cabut verifikasi (status menjadi 'suspended', alasan wajib)
     * Pencabutan otomatis menghentikan poin event & streak baru saat check-in.
     */
    public function revokeVerification(Request $request, Community $community)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reason.required' => 'Alasan pencabutan verifikasi wajib diisi.',
            'reason.min'      => 'Alasan pencabutan minimal 10 karakter agar jelas bagi pemilik komunitas.',
        ]);

        if ($community->verification_status !== 'verified') {
            return back()->with('error', 'Hanya komunitas berstatus Terverifikasi (verified) yang dapat dicabut verifikasinya.');
        }

        $oldValues = [
            'verification_status' => $community->verification_status,
            'verified_at'         => $community->verified_at,
            'rejection_reason'    => $community->rejection_reason,
        ];

        DB::transaction(function () use ($community, $validated, $oldValues, $request) {
            // Set status ke 'suspended' (sesuai ENUM di database.md) dan simpan alasan ke rejection_reason
            $community->verification_status = 'suspended';
            $community->rejection_reason    = $validated['reason'];
            $community->verified_at         = null;
            $community->save();

            // Catat ke audit_logs
            DB::table('audit_logs')->insert([
                'user_id'        => Auth::id(),
                'action'         => 'community.verification_revoked',
                'auditable_type' => 'community',
                'auditable_id'   => $community->id,
                'old_values'     => json_encode($oldValues),
                'new_values'     => json_encode([
                    'verification_status' => 'suspended',
                    'rejection_reason'    => $validated['reason'],
                ]),
                'ip_address'     => $request->ip(),
                'user_agent'     => substr((string) $request->userAgent(), 0, 255),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return back()->with('success', "Verifikasi komunitas \"{$community->name}\" telah dicabut (Suspended). Poin event dan streak baru otomatis dihentikan saat check-in.");
    }
}