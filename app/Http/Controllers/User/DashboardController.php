<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard user.
     * Menggunakan data asli dari database sesuai kaidah MVC standard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Data Asli Komunitas dari Database (mengambil seluruh komunitas yang aktif)
        $communities = Community::where('is_active', true)
            ->with(['owner', 'schedules', 'events'])
            ->withCount(['members', 'schedules', 'events'])
            ->latest()
            ->get();

        // Data Asli Event Mendatang dari Database
        $events = Event::where('is_active', true)
            ->with('community')
            ->orderBy('event_date', 'asc')
            ->get();

        // Data Asli Jadwal Kegiatan Rutin dari Database
        $schedules = Schedule::where('is_active', true)
            ->with('community')
            ->get();

        // Data Keikutsertaan Komunitas User
        $userMemberships = $user
            ? $user->communityMemberships()->with('community')->get()
            : collect();

        // Agregasi Statistik Asli
        $userCommunitiesCount = $userMemberships->count();
        $totalCommunitiesCount = $communities->count();
        $totalEventsCount = $events->count();
        $totalSchedulesCount = $schedules->count();

        // Ambil daftar kategori cabor unik dari database komunitas asli
        $sportCategories = $communities->pluck('sport_category')->filter()->unique()->values();

        // Ambil daftar kota unik dari database komunitas asli
        $cities = $communities->pluck('city')->filter()->unique()->values();

        return view('user.dashboard', compact(
            'user',
            'communities',
            'events',
            'schedules',
            'userMemberships',
            'userCommunitiesCount',
            'totalCommunitiesCount',
            'totalEventsCount',
            'totalSchedulesCount',
            'sportCategories',
            'cities'
        ));
    }
}
