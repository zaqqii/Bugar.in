<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\Event;
use App\Models\Participant;
use App\Models\PointsHistory;
use App\Models\Schedule;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard user.
     * Menggunakan data asli dari database sesuai kaidah MVC standard.
     */
    public function followCommunity(Community $community)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $community->followers()->syncWithoutDetaching([$user->id]);
        $followersCount = $community->followers()->count();
        $updateData = ['followers_count' => $followersCount];

        // Otomatis verifikasi jika mencapai minimal 15 pengikut
        if ($followersCount >= 15 && $community->verification_status !== 'verified') {
            $updateData['verification_status'] = 'verified';
            $updateData['verified_at'] = now();
            if (\Illuminate\Support\Facades\Schema::hasColumn('communities', 'verification_type')) {
                $updateData['verification_type'] = 'free_followers';
            }
        }

        $community->update($updateData);

        return back()->with('success', 'Berhasil mengikuti komunitas.');
    }

    public function unfollowCommunity(Community $community)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $community->followers()->detach($user->id);
        $community->update([
            'followers_count' => $community->followers()->count(),
        ]);

        return back()->with('success', 'Anda batal mengikuti komunitas.');
    }

    public function joinSchedule(Schedule $schedule)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $schedule->participants()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'participatable_type' => Schedule::class,
            'participatable_id' => $schedule->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        return back()->with('success', 'Berhasil mendaftar sesi latihan.');
    }

    public function joinEvent(Event $event)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $event->participants()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'participatable_type' => Event::class,
            'participatable_id' => $event->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        return back()->with('success', 'Berhasil mendaftar event.');
    }

    public function redeemReward($rewardId)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        return back()->with('info', 'Fitur penukaran reward sedang dalam tahap pengembangan.');
    }

    public function streakCheckIn(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (! $user instanceof User) {
            $user = User::find(Auth::id());
        }

        // Cek apakah user sudah check-in hari ini
        $today = now()->toDateString();
        $alreadyCheckedIn = $user->pointsHistories()
            ->whereDate('created_at', $today)
            ->where('source', 'attendance')
            ->exists();

        if ($alreadyCheckedIn) {
            return back()->with('info', 'Anda sudah melakukan check-in untuk hari ini.');
        }

        return DB::transaction(function () use ($user, $today) {
            $todayName = strtolower(now()->englishDayOfWeek);

            // Filter komunitas yang eligible (terverifikasi atau followers >= 10)
            $eligibleCommunity = fn($query) => $query->where('verification_status', 'verified')
                ->orWhere('followers_count', '>=', 10);

            // 1. Cari sesi yang diikuti user (Event atau Schedule dari komunitas eligible)
            $participant = Participant::where('user_id', $user->id)
                ->whereHasMorph('participatable', [Event::class, Schedule::class], function ($query) use ($eligibleCommunity) {
                    $query->whereHas('community', $eligibleCommunity);
                })
                ->latest()
                ->first();

            // Jika belum ada participant, coba hubungkan ke jadwal hari ini dari komunitas yang di-follow / eligible
            if (! $participant) {
                $followedCommunityIds = $user->followedCommunities()->pluck('communities.id')->all();

                $schedule = Schedule::whereIn('community_id', $followedCommunityIds)
                    ->where('is_active', true)
                    ->where(function ($q) use ($todayName) {
                        $q->where('day_of_week', $todayName)
                          ->orWhereNull('day_of_week');
                    })
                    ->first();

                if (! $schedule) {
                    $schedule = Schedule::whereHas('community', $eligibleCommunity)
                        ->where('is_active', true)
                        ->first();
                }

                if ($schedule) {
                    $participant = Participant::firstOrCreate([
                        'user_id' => $user->id,
                        'participatable_type' => Schedule::class,
                        'participatable_id' => $schedule->id,
                    ], [
                        'status' => 'attended',
                        'registered_at' => now(),
                    ]);
                }
            }

            $pointable = null;
            $sportCategory = 'Kebugaran';

            if ($participant) {
                $participant->update(['status' => 'attended']);

                $attendance = Attendance::firstOrCreate(
                    ['participant_id' => $participant->id],
                    [
                        'check_in_at' => now(),
                        'method' => 'manual',
                    ]
                );
                $attendance->update(['check_in_at' => now()]);
                $pointable = $attendance;

                $activity = $participant->participatable;
                $sportCategory = $activity?->community?->sport_category ?? $sportCategory;
            }

            // Update atau buat streak untuk user
            $streak = Streak::firstOrCreate(
                ['user_id' => $user->id, 'sport_category' => $sportCategory],
                [
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'streak_started_at' => now(),
                ]
            );

            $lastDate = $streak->last_activity_date ? \Carbon\Carbon::parse($streak->last_activity_date) : null;
            if (! $lastDate || $lastDate->lt(now()->startOfDay()->subDay())) {
                $streak->current_streak = 1;
            } elseif ($lastDate->isYesterday()) {
                $streak->current_streak += 1;
            }

            $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
            $streak->last_activity_date = $today;
            $streak->save();

            if (! $pointable) {
                $pointable = $streak;
            }

            // Tambah poin user
            $newBalance = ($user->total_points ?? 0) + 30;
            $user->total_points = $newBalance;
            $user->save();

            // Catat ke riwayat poin dengan polymorphic pointable
            $pointsHistory = new PointsHistory([
                'user_id' => $user->id,
                'points' => 30,
                'source' => 'attendance',
                'description' => 'Check-in sesi olahraga harian (+30 Poin)',
                'balance_after' => $newBalance,
            ]);
            $pointsHistory->pointable()->associate($pointable);
            $pointsHistory->save();

            return back()->with('success', '⚡ Berhasil check-in sesi hari ini! +30 Poin berhasil ditambahkan.');
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Data Asli Komunitas dari Database (mengambil seluruh komunitas yang aktif)
        $communities = Community::where('is_active', true)
            ->with(['owner', 'schedules', 'events'])
            ->withCount(['members', 'schedules', 'events', 'followers'])
            ->latest()
            ->get();

        // Komunitas Terbaik (diurutkan berdasarkan status verifikasi dan anggota terbanyak)
        $topCommunities = $communities->sortBy([
            fn($a, $b) => ($b->verification_status === 'verified' ? 1 : 0) <=> ($a->verification_status === 'verified' ? 1 : 0),
            fn($a, $b) => ($b->members_count ?? 0) <=> ($a->members_count ?? 0),
            fn($a, $b) => ($b->followers_count ?? 0) <=> ($a->followers_count ?? 0),
        ])->take(3)->values();

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
            'date' => $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') : null,
            'day' => null,
            'start' => $event->start_time ? substr($event->start_time, 0, 5) : null,
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

        // Leaderboard overall memakai poin dan aktivitas yang tercatat di database.
        $leaderboardUsers = User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->select(['id', 'name', 'username', 'avatar', 'total_points'])
            ->withCount([
                'participants as attended_sessions_count' => fn($query) => $query->where('status', 'attended'),
                'communityMemberships as active_communities_count' => fn($query) => $query->where('status', 'active'),
            ])
            ->withMax('streaks as current_streak', 'current_streak')
            ->withMax('streaks as longest_streak', 'longest_streak')
            ->orderByDesc('total_points')
            ->orderByDesc('attended_sessions_count')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function (User $rankedUser, int $index) use ($user) {
                $rankedUser->setAttribute('rank', $index + 1);
                $rankedUser->setAttribute('is_current_user', $user && $rankedUser->id === $user->id);

                return $rankedUser;
            });

        $leaderboardStats = [
            'users' => $leaderboardUsers->count(),
            'points' => $leaderboardUsers->sum('total_points'),
            'sessions' => $leaderboardUsers->sum('attended_sessions_count'),
            'active_streaks' => $leaderboardUsers->where('current_streak', '>', 0)->count(),
        ];

        // Streak, poin, dan aktivitas user hanya memakai kehadiran yang diverifikasi.
        $userId = $user instanceof User ? $user->id : null;
        $attendanceForUser = $userId
            ? fn($query) => $query
                ->where('user_id', $userId)
                ->where('status', 'attended')
            : fn($query) => $query->whereRaw('0 = 1');

        $eligibleCommunity = fn($query) => $query->where('verification_status', 'verified')
            ->orWhere('followers_count', '>=', 10);
        $eligibleActivity = fn($query) => $query->whereHasMorph(
            'participatable',
            [Event::class, Schedule::class],
            fn($activityQuery) => $activityQuery->whereHas('community', $eligibleCommunity)
        );

        $verifiedAttendanceCount = Attendance::query()
            ->when($userId, fn($query) => $query
                ->whereHas('participant', $attendanceForUser)
                ->whereHas('participant', fn($query) => $attendanceForUser($query)->where($eligibleActivity)))
            ->when(!$userId, fn($query) => $query->whereRaw('0 = 1'))
            ->count();

        $verifiedActivities = Attendance::query()
            ->when($userId, fn($query) => $query
                ->whereHas('participant', $attendanceForUser)
                ->whereHas('participant', fn($query) => $attendanceForUser($query)->where($eligibleActivity)))
            ->when(!$userId, fn($query) => $query->whereRaw('0 = 1'))
            ->with('participant.participatable.community')
            ->orderByRaw('COALESCE(check_in_at, attendances.created_at) DESC')
            ->limit(100)
            ->get()
            ->map(function (Attendance $attendance) {
                $activity = $attendance->participant?->participatable;
                $checkedAt = $attendance->check_in_at ?? $attendance->created_at;

                return [
                    'id' => $attendance->id,
                    'date' => $checkedAt ? $checkedAt->format('Y-m-d') : null,
                    'time' => $checkedAt ? $checkedAt->format('H:i') : null,
                    'title' => $activity?->title ?? 'Sesi olahraga',
                    'community' => $activity?->community?->name ?? 'Komunitas',
                    'type' => $activity instanceof Event ? $activity->type : 'Latihan rutin',
                    'method' => $attendance->method,
                ];
            });

        $streakRecords = $user instanceof User ? $user->streaks()->orderByDesc('current_streak')->get() : collect();
        $eligibleDates = $verifiedActivities->pluck('date')->filter()->unique()->sort()->values();
        $longestEligibleStreak = 0;
        $runningEligibleStreak = 0;
        $previousDate = null;
        foreach ($eligibleDates as $activityDate) {
            $day = \Carbon\Carbon::parse($activityDate);
            $runningEligibleStreak = $previousDate && $previousDate->copy()->addDay()->isSameDay($day)
                ? $runningEligibleStreak + 1
                : 1;
            $longestEligibleStreak = max($longestEligibleStreak, $runningEligibleStreak);
            $previousDate = $day;
        }
        $lastEligibleDate = $eligibleDates->isNotEmpty() ? \Carbon\Carbon::parse($eligibleDates->last()) : null;
        $currentEligibleStreak = $lastEligibleDate && $lastEligibleDate->greaterThanOrEqualTo(now()->startOfDay()->subDay())
            ? $runningEligibleStreak
            : 0;
        $streakStats = [
            'current' => $currentEligibleStreak,
            'longest' => $longestEligibleStreak,
            'sessions' => $verifiedAttendanceCount,
            'sports' => $streakRecords->count(),
        ];

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $streakStats['active_days_this_week'] = $verifiedActivities
            ->pluck('date')
            ->filter(fn($date) => $date && $date >= $weekStart && $date <= $weekEnd)
            ->unique()
            ->count();
        $streakActivityDates = $verifiedActivities->pluck('date')->filter()->unique()->values();
        $weeklyStreakDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $streakActivityDates) {
            $date = \Carbon\Carbon::parse($weekStart)->addDays($offset);

            return [
                'label' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'][$offset],
                'date' => $date->toDateString(),
                'completed' => $streakActivityDates->contains($date->toDateString()),
                'is_today' => $date->isToday(),
            ];
        });
        $streakMilestones = collect([7, 21, 30])->map(fn(int $target) => [
            'target' => $target,
            'progress' => min($streakStats['current'], $target),
            'percentage' => min(100, (int) round(($streakStats['current'] / $target) * 100)),
            'completed' => $streakStats['longest'] >= $target,
        ]);

        $pointsHistory = $user instanceof User
            ? $user->pointsHistories()->latest()->limit(12)->get()
            : collect();
        $pointsStats = [
            'balance' => (int) ($user instanceof User ? ($user->total_points ?? 0) : 0),
            'earned_this_month' => (int) ($user instanceof User
                ? $user->pointsHistories()
                    ->where('points', '>', 0)
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('points')
                : 0),
        ];

        $pointRewards = collect([]);

        // Data Keikutsertaan Komunitas User
        $userMemberships = $user instanceof User
            ? $user->communityMemberships()->with('community')->get()
            : collect();
        $followedCommunityIds = $user instanceof User
            ? $user->followedCommunities()->pluck('communities.id')->all()
            : [];

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
            'leaderboardUsers',
            'leaderboardStats',
            'verifiedActivities',
            'streakRecords',
            'streakStats',
            'streakActivityDates',
            'weeklyStreakDays',
            'streakMilestones',
            'pointsHistory',
            'pointsStats',
            'pointRewards',
            'userMemberships',
            'followedCommunityIds',
            'userCommunitiesCount',
            'totalCommunitiesCount',
            'totalEventsCount',
            'totalSchedulesCount',
            'sportCategories',
            'cities',
            'topCommunities'
        ));
    }
}