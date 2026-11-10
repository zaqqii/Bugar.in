<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Console Admin dengan data operasional dinamis.
     */
    public function index()
    {
        // 1. Agregasi Metrik & KPI Platform
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'new_users_this_month' => User::where('role', 'user')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'total_community_owners' => User::where('role', 'community_owner')->count(),
            'total_communities' => Community::count(),
            'verified_communities' => Community::where('verification_status', 'verified')->count(),
            'pending_verifications' => Community::where('verification_status', 'pending')->count(),
            'total_events' => Event::count(),
            'active_events' => Event::whereIn('status', ['published', 'ongoing'])->count(),
            'total_schedules' => Schedule::count(),
            'total_participations' => Participant::count(),
        ];

        // 2. Daftar Permohonan Verifikasi Komunitas Tertunda (Pending Queue)
        $pendingCommunities = Community::where('verification_status', 'pending')
            ->with('owner')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 3. Komunitas Terbaru
        $recentCommunities = Community::with('owner')
            ->latest()
            ->take(6)
            ->get();

        // 4. Event Terbaru & Terjadwal
        $recentEvents = Event::with('community')
            ->latest()
            ->take(6)
            ->get();

        // 5. Pendaftar Akun Pengguna Terbaru
        $recentUsers = User::latest()
            ->take(8)
            ->get();

        // 6. Distribusi Cabang Olahraga Terpopuler
        $sportDistribution = Community::selectRaw('sport_category, count(*) as count')
            ->groupBy('sport_category')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingCommunities',
            'recentCommunities',
            'recentEvents',
            'recentUsers',
            'sportDistribution'
        ));
    }

    /**
     * Setujui verifikasi komunitas (Quick Action Admin).
     */
    public function approveVerification($id)
    {
        $community = Community::findOrFail($id);
        $community->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Verifikasi untuk komunitas '{$community->name}' berhasil disetujui.");
    }

    /**
     * Tolak verifikasi komunitas (Quick Action Admin).
     */
    public function rejectVerification(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $reason = $request->input('reason', 'Informasi profil atau dokumen komunitas belum memenuhi standar regulasi.');

        $community->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return back()->with('success', "Pengajuan verifikasi komunitas '{$community->name}' telah ditolak.");
    }

    /**
     * Toggle status aktif komunitas.
     */
    public function toggleCommunityStatus($id)
    {
        $community = Community::findOrFail($id);
        $community->update([
            'is_active' => !$community->is_active,
        ]);

        $statusText = $community->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Status komunitas '{$community->name}' berhasil {$statusText}.");
    }
}

