<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Tampilkan halaman data pengguna dengan ringkasan, grafik, filter, dan pagination.
     */
    public function index(Request $request)
    {
        // 1. Ringkasan Metrik / KPI Pengguna
        $stats = [
            'total_users' => User::count(),
            'regular_users' => User::where('role', 'user')->count(),
            'community_owners' => User::where('role', 'community_owner')->count(),
            'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'active_users' => User::where('is_active', true)->count(),
            'users_in_communities' => User::where('role', 'user')->whereHas('joinedCommunities')->count(),
            'total_points_distributed' => User::sum('total_points'),
        ];

        // 2. Data Grafik Pendaftaran 6 Bulan Terakhir
        $registrationChart = [
            'labels' => [],
            'data' => [],
        ];
        
        $centerDate = now()->copy();
        for ($i = -3; $i <= 3; $i++) {
            $date = $centerDate->copy()->addMonths($i);
            $label = $date->isoFormat('MMM YYYY');
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $registrationChart['labels'][] = $label;
            $registrationChart['data'][] = $count;
        }

        // 3. Data Grafik Distribusi Peran Pengguna
        $roleDistribution = [
            'user' => $stats['regular_users'],
            'community_owner' => $stats['community_owners'],
            'admin' => User::where('role', 'admin')->count(),
        ];

        // 4. Query Data Pengguna dengan Filter
        $query = User::query();

        // Pencarian (Nama, Username, Email, Telepon)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter Role
        if ($request->filled('role') && in_array($request->role, ['user', 'community_owner', 'admin'])) {
            $query->where('role', $request->role);
        }

        // Filter Status Aktif
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter Status Bergabung Komunitas
        if ($request->filled('community_status')) {
            if ($request->community_status === 'has_community') {
                $query->whereHas('joinedCommunities');
            } elseif ($request->community_status === 'no_community') {
                $query->whereDoesntHave('joinedCommunities');
            }
        }

        // Filter Komunitas Spesifik
        if ($request->filled('community_id')) {
            $query->whereHas('joinedCommunities', function ($q) use ($request) {
                $q->where('communities.id', $request->community_id);
            });
        }

        // Sorting
        switch ($request->get('sort', 'latest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'points_high':
                $query->orderByDesc('total_points');
                break;
            case 'points_low':
                $query->orderBy('total_points');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Eager-loading relasi komunitas dan pagination
        $users = $query->with([
            'joinedCommunities' => function ($q) {
                $q->select('communities.id', 'communities.name', 'communities.slug', 'communities.sport_category', 'communities.city', 'communities.logo');
            },
            'ownedCommunities' => function ($q) {
                $q->select('communities.id', 'communities.owner_id', 'communities.name', 'communities.slug', 'communities.sport_category', 'communities.city', 'communities.verification_status', 'communities.is_active', 'communities.members_count', 'communities.followers_count');
            }
        ])->paginate(10)->withQueryString();

        // Daftar semua komunitas aktif untuk opsi filter dropdown
        $communitiesList = Community::where('is_active', true)
            ->orderBy('name')
            ->select('id', 'name', 'sport_category')
            ->get();

        // Jika request via AJAX / Fetch dari Alpine.js
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users', compact(
                    'users',
                    'stats',
                    'registrationChart',
                    'roleDistribution',
                    'communitiesList'
                ))->fragment('users-table'),
                'total' => $users->total(),
            ]);
        }

        return view('admin.users', compact(
            'users',
            'stats',
            'registrationChart',
            'roleDistribution',
            'communitiesList'
        ));
    }

    /**
     * Toggle status aktif/nonaktif akun pengguna.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan saat ini.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan sementara';
        return back()->with('success', "Status akun '{$user->name}' berhasil {$statusText}.");
    }
}

