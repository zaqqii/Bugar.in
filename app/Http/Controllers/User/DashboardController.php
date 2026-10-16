<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityMember;
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

        // Agenda kalender menggabungkan event bertanggal dan latihan mingguan.
        $agendaItems = $events->map(fn(Event $event) => [
            'id' => 'event-' . $event->id,
            'kind' => 'event',
            'type' => $event->type,
            'title' => $event->title,
            'description' => $event->description,
            'community' => $event->community?->name ?? 'Komunitas',
            'sport' => $event->community?->sport_category ?? 'olahraga',
            'location' => $event->location,
            'maps_url' => $event->maps_url,
            'date' => $event->event_date?->format('Y-m-d'),
            'day' => null,
            'start' => substr($event->start_time, 0, 5),
            'end' => $event->end_time ? substr($event->end_time, 0, 5) : null,
            'capacity' => $event->capacity,
            'filled' => $event->participants_count,
            'status' => $event->status,
        ]);

        $agendaItems = $agendaItems->concat($schedules->map(fn(Schedule $schedule) => [
            'id' => 'schedule-' . $schedule->id,
            'kind' => 'schedule',
            'type' => 'latihan_rutin',
            'title' => $schedule->title,
            'description' => $schedule->description,
            'community' => $schedule->community?->name ?? 'Komunitas',
            'sport' => $schedule->community?->sport_category ?? 'olahraga',
            'location' => $schedule->location,
            'maps_url' => $schedule->maps_url,
            'date' => null,
            'day' => ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6][$schedule->day_of_week] ?? null,
            'start' => substr($schedule->start_time, 0, 5),
            'end' => substr($schedule->end_time, 0, 5),
            'capacity' => $schedule->capacity,
            'filled' => $schedule->filledCount(),
            'status' => $schedule->status,
        ]))->values();

        // Data Keikutsertaan Komunitas User
        $userMemberships = $user
            ? CommunityMember::where('user_id', $user->id)->with('community')->get()
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
            'agendaItems',
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
