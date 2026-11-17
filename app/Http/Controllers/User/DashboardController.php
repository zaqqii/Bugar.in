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
use App\Services\PointsAwardService;
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

    public function joinSchedule(Schedule $schedule, PointsAwardService $pointsAwardService)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $result = DB::transaction(function () use ($user, $schedule, $pointsAwardService): array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $existing = Participant::query()
                ->where('user_id', $lockedUser->id)
                ->where('participatable_type', Schedule::class)
                ->where('participatable_id', $schedule->id)
                ->lockForUpdate()
                ->first();

            if ($existing && in_array($existing->status, ['waiting_list', 'registered', 'attended'], true)) {
                return ['already_joined' => true, 'points' => 0];
            }

            if ($existing) {
                $existing->update([
                    'status' => 'waiting_list',
                    'registered_at' => now(),
                    'cancelled_at' => null,
                ]);
            } else {
                Participant::create([
                    'user_id' => $lockedUser->id,
                    'participatable_type' => Schedule::class,
                    'participatable_id' => $schedule->id,
                    'status' => 'waiting_list',
                    'registered_at' => now(),
                ]);
            }

            return [
                'already_joined' => false,
                'points' => $pointsAwardService->awardScheduleParticipation($lockedUser, $schedule),
            ];
        });

        if ($result['already_joined']) {
            return back()->with('info', 'Kamu sudah mendaftar sesi ini. Menunggu konfirmasi dari penyedia komunitas.');
        }

        $message = 'Permintaan bergabung sesi latihan dikirim! Menunggu konfirmasi penyedia komunitas.';
        if ($result['points'] > 0) {
            $message .= ' Kamu mendapat ' . $result['points'] . ' Poin Gerak.';
        }

        return back()->with('success', $message);
    }

    public function joinEvent(Event $event)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // Cek apakah sudah terdaftar
        $existing = Participant::where('user_id', $user->id)
            ->where('participatable_type', Event::class)
            ->where('participatable_id', $event->id)
            ->first();

        if ($existing) {
            if (in_array($existing->status, ['waiting_list', 'waiting_payment', 'registered', 'attended'])) {
                $label = $existing->statusLabel();
                return back()->with('info', "Kamu sudah mendaftar event ini. Status: {$label}.");
            }
            // Daftar ulang jika sebelumnya dibatalkan
            $newStatus = $event->registration_fee > 0 ? 'waiting_payment' : 'waiting_list';
            $existing->update([
                'status'        => $newStatus,
                'registered_at' => now(),
                'cancelled_at'  => null,
            ]);
            $label = $existing->fresh()->statusLabel();
            return back()->with('success', "Pendaftaran event dikirim! Status: {$label}.");
        }

        // Event berbayar → waiting_payment, gratis → waiting_list (menunggu konfirmasi)
        $status = $event->registration_fee > 0 ? 'waiting_payment' : 'waiting_list';

        Participant::create([
            'user_id'              => $user->id,
            'participatable_type'  => Event::class,
            'participatable_id'    => $event->id,
            'status'               => $status,
            'qr_code'              => 'TKT-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . $user->id . '-' . $event->id,
            'registered_at'        => now(),
        ]);

        if ($event->registration_fee > 0) {
            $fee = 'Rp ' . number_format($event->registration_fee, 0, ',', '.');
            return back()->with('success', "Pendaftaran event berhasil! Silakan selesaikan pembayaran sebesar {$fee} kepada penyelenggara. Status: Menunggu Pembayaran.");
        }

        return back()->with('success', 'Permintaan bergabung event dikirim! Menunggu konfirmasi dari penyedia komunitas.');
    }

    public function cancelParticipation(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $type = $request->input('type'); // 'schedule' or 'event'
        $id   = $request->input('id');

        $morphType = $type === 'event' ? Event::class : Schedule::class;

        $participant = Participant::where('user_id', $user->id)
            ->where('participatable_type', $morphType)
            ->where('participatable_id', $id)
            ->whereIn('status', ['waiting_list', 'waiting_payment', 'registered'])
            ->first();

        if (! $participant) {
            return back()->with('info', 'Tidak ada pendaftaran aktif yang dapat dibatalkan.');
        }

        $participant->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Pendaftaran berhasil dibatalkan.');
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

        $rawCode = trim((string) $request->input('qr_code', ''));

        if (empty($rawCode)) {
            return back()->with('streak_error', 'Silakan pindai kamera QR atau masukkan kode QR event.');
        }

        // 1. Identifikasi Event dan Participant dari kode QR
        $event = null;
        $participant = null;

        // Cek apakah rawCode adalah kode tiket peserta (TKT-...)
        $ticketParticipant = Participant::where('qr_code', $rawCode)
            ->where('participatable_type', Event::class)
            ->first();

        if ($ticketParticipant) {
            if ($ticketParticipant->user_id !== $user->id) {
                return back()->with('streak_error', 'Kode tiket QR ini milik peserta lain.');
            }
            $participant = $ticketParticipant;
            $event = $ticketParticipant->participatable;
        }

        // Jika bukan tiket peserta, cek apakah kode QR adalah identifier Event
        if (! $event) {
            $cleanCode = $rawCode;
            if (str_contains($cleanCode, '/events/')) {
                $cleanCode = basename(parse_url($cleanCode, PHP_URL_PATH));
            }

            if (preg_match('/^(?:BUGARIN[-_]EVENT[-_]|EVT[-_])(\d+)$/i', $cleanCode, $matches)) {
                $event = Event::find($matches[1]);
            } elseif (is_numeric($cleanCode)) {
                $event = Event::find($cleanCode);
            } else {
                $event = Event::where('slug', $cleanCode)->first();
            }
        }

        if (! $event) {
            return back()->with('streak_error', 'Kode QR tidak dikenali sebagai event resmi Bugar.in.');
        }

        // 2. Validasi Event: HANYA event dari komunitas yang SUDAH TERVERIFIKASI
        $community = $event->community;
        if (! $community || ! $community->isVerified()) {
            return back()->with('streak_error', 'Check-in streak ditolak: Streak hanya bisa diperoleh dari event komunitas yang sudah terverifikasi resmi.');
        }

        // Event harus aktif
        if (! in_array($event->status, ['published', 'ongoing', 'completed'])) {
            return back()->with('streak_error', 'Event ini belum aktif atau telah dibatalkan.');
        }

        // Toleransi tanggal event: pada hari event_date (atau jika status ongoing)
        if ($event->event_date && ! $event->event_date->isToday() && $event->status !== 'ongoing') {
            $formattedDate = $event->event_date->translatedFormat('d F Y');
            return back()->with('streak_error', "Check-in QR streak hanya dapat dilakukan pada hari pelaksanaan event ({$formattedDate}).");
        }

        // 3. Cek pendaftaran peserta
        if (! $participant) {
            $participant = Participant::where('user_id', $user->id)
                ->where('participatable_type', Event::class)
                ->where('participatable_id', $event->id)
                ->first();
        }

        if (! $participant) {
            if ($event->isFull()) {
                return back()->with('streak_error', 'Kamu belum terdaftar di event ini dan kuota peserta sudah penuh.');
            }

            $participant = Participant::create([
                'user_id'             => $user->id,
                'participatable_type' => Event::class,
                'participatable_id'   => $event->id,
                'status'              => 'registered',
                'qr_code'             => 'TKT-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . $user->id . '-' . $event->id,
                'registered_at'       => now(),
            ]);
        }

        if ($participant->status === 'cancelled') {
            return back()->with('streak_error', 'Pendaftaran kamu pada event ini telah dibatalkan.');
        }

        // Cek apakah sudah pernah check-in di event yang sama
        $alreadyAttended = Attendance::where('participant_id', $participant->id)->exists();
        if ($alreadyAttended && $participant->status === 'attended') {
            return back()->with('info', "Kehadiran QR kamu untuk event '{$event->title}' sudah tercatat sebelumnya.");
        }

        // 4. Proses Kehadiran QR & Streak Mingguan
        return DB::transaction(function () use ($user, $event, $participant) {
            $participant->update(['status' => 'attended']);

            $attendance = Attendance::firstOrCreate(
                ['participant_id' => $participant->id],
                [
                    'check_in_at' => now(),
                    'method'      => 'qr_scan',
                ]
            );
            $attendance->update([
                'check_in_at' => now(),
                'method'      => 'qr_scan',
            ]);

            $sportCategory = $event->community->sport_category ?? 'Kebugaran';

            $streak = Streak::firstOrCreate(
                ['user_id' => $user->id, 'sport_category' => $sportCategory],
                [
                    'current_streak'    => 0,
                    'longest_streak'    => 0,
                    'streak_started_at' => now()->toDateString(),
                ]
            );

            // Logika Streak MINGGUAN (Weekly Streak)
            $now = now();
            $thisWeekStart = $now->copy()->startOfWeek()->toDateString();
            $thisWeekEnd   = $now->copy()->endOfWeek()->toDateString();
            $lastWeekStart = $now->copy()->subWeek()->startOfWeek()->toDateString();
            $lastWeekEnd   = $now->copy()->subWeek()->endOfWeek()->toDateString();

            $lastDate = $streak->last_activity_date ? \Carbon\Carbon::parse($streak->last_activity_date)->toDateString() : null;
            $alreadyCountedThisWeek = false;

            if ($lastDate && $lastDate >= $thisWeekStart && $lastDate <= $thisWeekEnd) {
                // Sudah tercatat pada pekan kalender yang sama -> tidak bertambah ganda
                $alreadyCountedThisWeek = true;
            } elseif ($lastDate && $lastDate >= $lastWeekStart && $lastDate <= $lastWeekEnd) {
                // Aktif di pekan lalu -> streak mingguan bertambah 1 pekan beruntun
                $streak->current_streak += 1;
                $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
                $streak->last_activity_date = $now->toDateString();
            } else {
                // Baru mulai atau jeda lebih dari 1 pekan -> mulai dari 1 pekan
                $streak->current_streak = 1;
                $streak->longest_streak = max($streak->longest_streak, 1);
                $streak->streak_started_at = $now->toDateString();
                $streak->last_activity_date = $now->toDateString();
            }

            $streak->save();

            // Poin reward event
            $pointsEarned = $event->points_reward > 0 ? (int) $event->points_reward : 50;
            $newBalance = ($user->total_points ?? 0) + $pointsEarned;
            $user->total_points = $newBalance;
            $user->save();

            $pointsHistory = new PointsHistory([
                'user_id'       => $user->id,
                'points'        => $pointsEarned,
                'source'        => 'attendance',
                'description'   => "Check-in QR Event: {$event->title} (+{$pointsEarned} Poin)",
                'balance_after' => $newBalance,
            ]);
            $pointsHistory->pointable()->associate($attendance);
            $pointsHistory->save();

            if ($alreadyCountedThisWeek) {
                return back()->with('success', "⚡ Check-in QR Event '{$event->title}' berhasil! (+{$pointsEarned} Poin). Kehadiran pekan ini sudah tercatat untuk menjaga streak.");
            }

            return back()->with('success', "🔥 Luar biasa! Check-in QR Event '{$event->title}' berhasil. Streak mingguan kamu bertambah menjadi {$streak->current_streak} pekan beruntun! (+{$pointsEarned} Poin)");
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

        // Streak, poin, dan aktivitas user HANYA memakai kehadiran yang diverifikasi dari EVENT komunitas terverifikasi resmi.
        $userId = $user instanceof User ? $user->id : null;
        $attendanceForUser = $userId
            ? fn($query) => $query
                ->where('user_id', $userId)
                ->where('status', 'attended')
            : fn($query) => $query->whereRaw('0 = 1');

        $eligibleCommunity = fn($query) => $query->where('verification_status', 'verified');
        $eligibleActivity = fn($query) => $query->whereHasMorph(
            'participatable',
            [Event::class],
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
            ->with(['participant.participatable.community'])
            ->orderByRaw('COALESCE(check_in_at, attendances.created_at) DESC')
            ->limit(100)
            ->get()
            ->map(function (Attendance $attendance) {
                $activity = $attendance->participant?->participatable;
                $checkedAt = $attendance->check_in_at ?? $attendance->created_at;

                return [
                    'id'        => $attendance->id,
                    'date'      => $checkedAt ? $checkedAt->format('Y-m-d') : null,
                    'time'      => $checkedAt ? $checkedAt->format('H:i') : null,
                    'title'     => $activity?->title ?? 'Event Olahraga',
                    'community' => $activity?->community?->name ?? 'Komunitas',
                    'type'      => $activity instanceof Event ? $activity->type : 'Event',
                    'method'    => $attendance->method,
                ];
            });

        // Perhitungan Streak MINGGUAN (Weekly Streak)
        $now = now();
        $thisWeekStart = $now->copy()->startOfWeek()->toDateString();
        $thisWeekEnd   = $now->copy()->endOfWeek()->toDateString();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek()->toDateString();
        $lastWeekEnd   = $now->copy()->subWeek()->endOfWeek()->toDateString();

        $streakRecords = $user instanceof User ? $user->streaks()->orderByDesc('current_streak')->get() : collect();
        $primaryStreak = $streakRecords->first();

        $currentWeeklyStreak = 0;
        $longestWeeklyStreak = $primaryStreak?->longest_streak ?? 0;

        if ($primaryStreak && $primaryStreak->last_activity_date) {
            $lastAct = \Carbon\Carbon::parse($primaryStreak->last_activity_date)->toDateString();
            if ($lastAct >= $lastWeekStart) {
                // Streak masih aktif karena ada aktivitas di pekan ini atau pekan lalu
                $currentWeeklyStreak = (int) $primaryStreak->current_streak;
            } else {
                // Terputus karena melewati pekan lalu tanpa aktivitas event
                $currentWeeklyStreak = 0;
            }
        }

        $hasAttendedThisWeek = $verifiedActivities->contains(function ($activity) use ($thisWeekStart, $thisWeekEnd) {
            return $activity['date'] && $activity['date'] >= $thisWeekStart && $activity['date'] <= $thisWeekEnd;
        });

        $streakStats = [
            'current'               => $currentWeeklyStreak,
            'longest'               => $longestWeeklyStreak,
            'sessions'              => $verifiedAttendanceCount,
            'sports'                => $streakRecords->count(),
            'attended_this_week'    => $hasAttendedThisWeek,
            'active_days_this_week' => $hasAttendedThisWeek ? 1 : 0,
        ];

        $streakActivityDates = $verifiedActivities->pluck('date')->filter()->unique()->values();
        $weeklyStreakDays = collect(range(0, 6))->map(function (int $offset) use ($thisWeekStart, $streakActivityDates) {
            $date = \Carbon\Carbon::parse($thisWeekStart)->addDays($offset);

            return [
                'label'     => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'][$offset],
                'date'      => $date->toDateString(),
                'completed' => $streakActivityDates->contains($date->toDateString()),
                'is_today'  => $date->isToday(),
            ];
        });

        // Milestone konsistensi mingguan
        $streakMilestones = collect([2, 4, 8, 12])->map(fn(int $target) => [
            'target'     => $target,
            'progress'   => min($streakStats['current'], $target),
            'percentage' => min(100, (int) round(($streakStats['current'] / $target) * 100)),
            'completed'  => $streakStats['longest'] >= $target,
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

        // Partisipasi aktif user (jadwal & event) untuk tombol status
        $userParticipants = ($user instanceof User)
            ? Participant::where('user_id', $user->id)
                ->whereIn('status', ['waiting_list', 'waiting_payment', 'registered', 'attended'])
                ->get()
                ->keyBy(fn($p) => $p->participatable_type . '-' . $p->participatable_id)
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

        // Event terverifikasi yang didaftarkan oleh user (untuk QR ticket / referensi check-in)
        $userEligibleEvents = ($user instanceof User)
            ? Participant::where('user_id', $user->id)
                ->where('participatable_type', Event::class)
                ->whereIn('status', ['registered', 'attended', 'waiting_list'])
                ->with(['participatable.community'])
                ->get()
                ->filter(fn($p) => $p->participatable && $p->participatable->community?->isVerified())
                ->map(function ($p) {
                    $event = $p->participatable;
                    return [
                        'participant_id'       => $p->id,
                        'qr_code'              => $p->qr_code ?? ('TKT-' . $p->id),
                        'status'               => $p->status,
                        'status_label'         => $p->statusLabel(),
                        'event_id'             => $event->id,
                        'event_title'          => $event->title,
                        'event_code'           => 'BUGARIN-EVENT-' . $event->id,
                        'event_date'           => $event->event_date ? $event->event_date->format('Y-m-d') : null,
                        'event_date_formatted' => $event->event_date ? $event->event_date->translatedFormat('d F Y') : '-',
                        'community_name'       => $event->community?->name ?? 'Komunitas',
                        'is_today'             => $event->event_date ? $event->event_date->isToday() : false,
                    ];
                })
                ->values()
            : collect();

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
            'topCommunities',
            'userParticipants',
            'userEligibleEvents'
        ));
    }
}
