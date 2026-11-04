<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\CommunityBlock;
use App\Models\Event;
use App\Models\Participant;
use App\Models\PointsHistory;
use App\Models\Schedule;
use App\Models\Streak;
use App\Models\User;
use App\Models\UserAnnouncement;
use App\Services\UserLevelService;
use Carbon\Carbon;
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

        if (! $user instanceof User) {
            $user = User::find(Auth::id());
        }

        return DB::transaction(function () use ($schedule, $user) {
            /** @var Schedule $lockedSchedule */
            $lockedSchedule = Schedule::where('id', $schedule->id)->lockForUpdate()->firstOrFail();

            // 1. Cek apakah jadwal aktif atau dibatalkan atau sudah selesai
            if (! $lockedSchedule->is_active || $lockedSchedule->isCancelled() || in_array($lockedSchedule->status, ['cancelled', 'completed'], true)) {
                return back()->with('error', 'Sesi latihan ini tidak dibuka untuk pendaftaran atau sudah dibatalkan.');
            }

            // 2. Cek apakah jadwal sudah dimulai
            if ($lockedSchedule->isStarted()) {
                return back()->with('error', 'Sesi latihan ini sudah dimulai atau telah selesai.');
            }

            // 3. Cek komunitas aktif
            if (! $lockedSchedule->community || ! $lockedSchedule->community->is_active) {
                return back()->with('error', 'Komunitas penyelenggara sesi ini sedang tidak aktif.');
            }

            // 4. Cek apakah user terkena join_blocked_until
            if ($user->isJoinBlocked()) {
                $until = $user->join_blocked_until->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d F Y, H:i');
                return back()->with('error', "Pendaftaran jadwal untuk akun Anda sedang dibatasi hingga {$until} WIB karena akumulasi ketidakhadiran (no-show).");
            }

            // 5. Cek apakah user diblokir oleh komunitas ini
            if (CommunityBlock::where('community_id', $lockedSchedule->community_id)->where('user_id', $user->id)->exists()) {
                return back()->with('error', 'Anda tidak dapat bergabung karena telah diblokir dari komunitas ini.');
            }

            // 6. Cek apakah sudah terdaftar aktif
            $existing = Participant::where('user_id', $user->id)
                ->where('participatable_type', Schedule::class)
                ->where('participatable_id', $lockedSchedule->id)
                ->lockForUpdate()
                ->first();

            if ($existing && in_array($existing->status, ['registered', 'attended'], true)) {
                return back()->with('info', 'Kamu sudah terdaftar di sesi latihan ini.');
            }

            // 7. Cek batas maksimal pendaftaran jadwal aktif mendatang (max_active_bookings)
            $maxActiveBookings = (int) config('schedules.max_active_bookings', 3);
            $todayStr = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
            $activeBookingsCount = Participant::where('user_id', $user->id)
                ->where('participatable_type', Schedule::class)
                ->where('status', 'registered')
                ->whereHasMorph('participatable', [Schedule::class], function ($q) use ($todayStr) {
                    $q->whereDate('date', '>=', $todayStr)
                      ->whereNull('cancelled_at')
                      ->where('status', '!=', 'cancelled');
                })
                ->count();

            if ($activeBookingsCount >= $maxActiveBookings) {
                return back()->with('error', "Kamu telah mencapai batas maksimal ({$maxActiveBookings}) pendaftaran jadwal aktif mendatang.");
            }

            // 8. Cek kuota / kapasitas (hanya jika capacity tidak NULL)
            $activeParticipantsCount = $lockedSchedule->activeParticipantsCount();
            if ($lockedSchedule->capacity !== null && $activeParticipantsCount >= $lockedSchedule->capacity) {
                return back()->with('error', 'Mohon maaf, kuota peserta untuk sesi latihan ini sudah penuh.');
            }

            // 9. Cek bentrok jam dengan jadwal lain di tanggal yang sama (startA < endB dan startB < endA)
            $scheduleDateStr = $lockedSchedule->date instanceof Carbon ? $lockedSchedule->date->format('Y-m-d') : (string) $lockedSchedule->date;
            $overlappingSchedule = Schedule::whereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'registered');
                })
                ->where('id', '!=', $lockedSchedule->id)
                ->whereDate('date', $scheduleDateStr)
                ->whereNull('cancelled_at')
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '<', $lockedSchedule->end_time)
                ->where('end_time', '>', $lockedSchedule->start_time)
                ->first();

            if ($overlappingSchedule) {
                $overlapStart = substr($overlappingSchedule->start_time, 0, 5);
                $overlapEnd = substr($overlappingSchedule->end_time, 0, 5);
                return back()->with('error', "Jadwal ini bertabrakan dengan jadwal '{$overlappingSchedule->title}' ({$overlapStart}-{$overlapEnd} WIB) yang sudah kamu ikuti pada tanggal yang sama.");
            }

            // 10. Simpan atau perbarui pendaftaran
            if ($existing) {
                $existing->update([
                    'status'        => 'registered',
                    'registered_at' => now(),
                    'cancelled_at'  => null,
                ]);
            } else {
                Participant::create([
                    'user_id'             => $user->id,
                    'participatable_type' => Schedule::class,
                    'participatable_id'   => $lockedSchedule->id,
                    'status'              => 'registered',
                    'registered_at'       => now(),
                ]);
            }

            // 11. Sinkronkan status jadwal
            $lockedSchedule->syncStatus();

            return back()->with('success', 'Selamat! Kamu berhasil bergabung dengan sesi latihan ini.');
        });
    }

    public function joinEvent(Event $event)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        return DB::transaction(function () use ($event, $user) {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->firstOrFail();

            // 1. Tolak jika status bukan published
            if ($lockedEvent->status !== 'published') {
                return back()->with('error', 'Pendaftaran untuk event ini tidak dibuka (status event: ' . ($lockedEvent->status) . ').');
            }

            // 2. Tolak jika di luar jendela registration_open_at / registration_close_at (null-safe)
            $now = now();
            if ($lockedEvent->registration_open_at && $now->lt($lockedEvent->registration_open_at)) {
                return back()->with('error', 'Pendaftaran belum dibuka (dibuka pada ' . $lockedEvent->registration_open_at->translatedFormat('d M Y, H:i') . ').');
            }

            if ($lockedEvent->registration_close_at) {
                $closeAt = $lockedEvent->registration_close_at->copy()->endOfDay();
                if ($now->gt($closeAt)) {
                    return back()->with('error', 'Pendaftaran untuk event ini sudah ditutup pada ' . $lockedEvent->registration_close_at->translatedFormat('d M Y') . '.');
                }
            }

            // 3. Cek apakah sudah terdaftar aktif
            $existing = Participant::where('user_id', $user->id)
                ->where('participatable_type', Event::class)
                ->where('participatable_id', $lockedEvent->id)
                ->lockForUpdate()
                ->first();

            if ($existing && in_array($existing->status, ['registered', 'waiting_payment', 'waiting_list', 'attended'], true)) {
                $label = $existing->statusLabel();
                return back()->with('info', "Kamu sudah terdaftar di event ini. Status saat ini: {$label}.");
            }

            // 4. Hitung kursi terpakai dari participant berstatus registered dan waiting_payment
            $usedSeats = Participant::where('participatable_type', Event::class)
                ->where('participatable_id', $lockedEvent->id)
                ->whereIn('status', ['registered', 'waiting_payment'])
                ->count();

            $isFull = $lockedEvent->capacity !== null && $usedSeats >= $lockedEvent->capacity;

            // 5. Tentukan status baru
            if ($isFull) {
                $newStatus = 'waiting_list';
            } else {
                $newStatus = (float) $lockedEvent->registration_fee > 0 ? 'waiting_payment' : 'registered';
            }

            // 6. Simpan / Perbarui pendaftaran
            if ($existing) {
                $existing->update([
                    'status'        => $newStatus,
                    'registered_at' => now(),
                    'cancelled_at'  => null,
                ]);
            } else {
                Participant::create([
                    'user_id'              => $user->id,
                    'participatable_type'  => Event::class,
                    'participatable_id'    => $lockedEvent->id,
                    'status'               => $newStatus,
                    'registered_at'        => now(),
                ]);
            }

            // 7. Increment events.participants_count hanya untuk status yang memakai kursi
            if (in_array($newStatus, ['registered', 'waiting_payment'], true)) {
                $lockedEvent->increment('participants_count');
            }

            if ($newStatus === 'waiting_list') {
                return back()->with('info', 'Kuota kursi event sudah penuh. Kamu dimasukkan ke dalam Waiting List (Daftar Tunggu).');
            } elseif ($newStatus === 'waiting_payment') {
                $fee = 'Rp ' . number_format($lockedEvent->registration_fee, 0, ',', '.');
                return back()->with('success', "Pendaftaran event berhasil! Silakan selesaikan pembayaran sebesar {$fee} kepada penyelenggara. Status: Menunggu Pembayaran.");
            } else {
                return back()->with('success', 'Selamat! Pendaftaran event berhasil. Kamu telah terdaftar resmi sebagai peserta.');
            }
        });
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

        return DB::transaction(function () use ($user, $morphType, $id, $type) {
            $participant = Participant::where('user_id', $user->id)
                ->where('participatable_type', $morphType)
                ->where('participatable_id', $id)
                ->whereIn('status', ['waiting_list', 'waiting_payment', 'registered'])
                ->lockForUpdate()
                ->first();

            if (! $participant) {
                return back()->with('info', 'Tidak ada pendaftaran aktif yang dapat dibatalkan.');
            }

            // Validasi batas waktu pembatalan untuk Schedule
            if ($type === 'schedule' || $morphType === Schedule::class) {
                $schedule = Schedule::where('id', $id)->lockForUpdate()->first();
                if ($schedule) {
                    $deadlineHours = (int) config('schedules.cancel_deadline_hours', 2);
                    $nowTz = Carbon::now(config('app.timezone', 'Asia/Jakarta'));
                    if ($schedule->startsAt()->isPast() || $nowTz->diffInMinutes($schedule->startsAt(), false) < ($deadlineHours * 60)) {
                        return back()->with('error', "Pembatalan jadwal hanya dapat dilakukan paling lambat {$deadlineHours} jam sebelum sesi dimulai.");
                    }
                }
            }

            $oldStatus = $participant->status;

            $participant->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Jika pembatalan event dan sebelumnya memakai kursi, kurangi participants_count
            if ($type === 'event' && in_array($oldStatus, ['registered', 'waiting_payment'], true)) {
                $event = Event::where('id', $id)->lockForUpdate()->first();
                if ($event && $event->participants_count > 0) {
                    $event->decrement('participants_count');
                }
            }

            // Jika pembatalan jadwal, sinkronkan status jadwal
            if ($type === 'schedule' || $morphType === Schedule::class) {
                $schedule = Schedule::where('id', $id)->lockForUpdate()->first();
                if ($schedule) {
                    $schedule->syncStatus();
                }
            }

            return back()->with('success', 'Pendaftaran berhasil dibatalkan.');
        });
    }

    public function readAnnouncement(UserAnnouncement $announcement)
    {
        abort_unless((int) $announcement->user_id === (int) Auth::id(), 404);

        $announcement->update(['read_at' => now()]);

        return back();
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
                $todayDate = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();

                // Catatan: Pencocokan jadwal manual hari ini adalah tambalan sementara dan akan digantikan oleh check-in QR pada rilis berikutnya.
                $schedule = Schedule::whereIn('community_id', $followedCommunityIds)
                    ->where('is_active', true)
                    ->whereNull('cancelled_at')
                    ->whereDate('date', $todayDate)
                    ->first();

                if (! $schedule) {
                    $schedule = Schedule::whereHas('community', $eligibleCommunity)
                        ->where('is_active', true)
                        ->whereNull('cancelled_at')
                        ->whereDate('date', $todayDate)
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

    public function index(Request $request, UserLevelService $levelService)
    {
        $user = Auth::user();

        // Data Asli Komunitas dari Database (mengambil seluruh komunitas yang aktif)
        $communities = Community::where('is_active', true)
            ->with(['owner', 'schedules' => fn($q) => $q->where('is_active', true)->whereNotIn('status', ['cancelled', 'completed']), 'events' => fn($q) => $q->active()])
            ->withCount(['members', 'schedules' => fn($q) => $q->where('is_active', true)->whereNotIn('status', ['cancelled', 'completed']), 'events' => fn($q) => $q->active(), 'followers'])
            ->latest()
            ->get();

        // Komunitas Terbaik (diurutkan berdasarkan status verifikasi dan anggota terbanyak)
        $topCommunities = $communities->sortBy([
            fn($a, $b) => ($b->verification_status === 'verified' ? 1 : 0) <=> ($a->verification_status === 'verified' ? 1 : 0),
            fn($a, $b) => ($b->members_count ?? 0) <=> ($a->members_count ?? 0),
            fn($a, $b) => ($b->followers_count ?? 0) <=> ($a->followers_count ?? 0),
        ])->take(3)->values();

        // Data Asli Event Mendatang dari Database (Hanya yang berstatus published, registration_closed, atau ongoing)
        $events = Event::where('is_active', true)
            ->active()
            ->with('community')
            ->orderBy('event_date', 'asc')
            ->get();

        $todayStr = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();

        // Jadwal yang diikuti user (status registered atau attended)
        $joinedScheduleIds = ($user instanceof User)
            ? Participant::where('user_id', $user->id)
                ->where('participatable_type', Schedule::class)
                ->whereIn('status', ['registered', 'attended'])
                ->pluck('participatable_id')
                ->all()
            : [];

        // Data Asli Jadwal Kegiatan Rutin dari Database:
        // Jadwal aktif & valid mendatang, ATAU seluruh jadwal yang diikuti user (termasuk riwayat selesai)
        $schedules = Schedule::query()
            ->where(function ($query) use ($todayStr, $joinedScheduleIds) {
                $query->where(function ($q) use ($todayStr) {
                    $q->where('is_active', true)
                      ->whereNull('cancelled_at')
                      ->whereNotIn('status', ['cancelled', 'completed'])
                      ->whereDate('date', '>=', $todayStr);
                });
                if (!empty($joinedScheduleIds)) {
                    $query->orWhereIn('id', $joinedScheduleIds);
                }
            })
            ->with('community')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Agenda kalender menggabungkan event bertanggal dan latihan bertanggal.
        $agendaItems = $events->map(fn(Event $event) => [
            'id' => 'event-' . $event->id,
            'raw_id' => $event->id,
            'kind' => 'event',
            'type' => $event->type,
            'title' => $event->title,
            'description' => $event->description,
            'community' => $event->community?->name ?? 'Komunitas',
            'sport' => $event->community?->sport_category ?? 'olahraga',
            'location' => $event->location,
            'maps_url' => $event->maps_url,
            'date' => $event->event_date ? Carbon::parse($event->event_date)->format('Y-m-d') : null,
            'date_formatted' => $event->event_date ? Carbon::parse($event->event_date)->locale('id')->isoFormat('dddd, D MMMM Y') : null,
            'day' => null,
            'start' => $event->start_time ? substr($event->start_time, 0, 5) : null,
            'end' => $event->end_time ? substr($event->end_time, 0, 5) : null,
            'capacity' => $event->capacity,
            'filled' => $event->participants_count,
            'status' => $event->status,
            'is_finished' => $event->event_date ? Carbon::parse($event->event_date->toDateString() . ' ' . ($event->end_time ?? '23:59:59'), config('app.timezone', 'Asia/Jakarta'))->isPast() : false,
            'streak_enabled' => false,
        ]);

        $agendaItems = $agendaItems->concat($schedules->map(fn(Schedule $schedule) => [
            'id' => 'schedule-' . $schedule->id,
            'raw_id' => $schedule->id,
            'kind' => 'schedule',
            'type' => 'latihan_rutin',
            'title' => $schedule->title,
            'level' => $schedule->level,
            'level_label' => $schedule->levelLabel(),
            'level_badge_class' => $schedule->levelBadgeClass(),
            'description' => $schedule->description,
            'community' => $schedule->community?->name ?? 'Komunitas',
            'sport' => $schedule->community?->sport_category ?? 'olahraga',
            'location' => $schedule->location,
            'maps_url' => $schedule->maps_url,
            'date' => $schedule->date ? $schedule->date->format('Y-m-d') : null,
            'date_formatted' => $schedule->date ? $schedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : $schedule->dayLabel(),
            'day' => null,
            'start' => substr($schedule->start_time, 0, 5),
            'end' => substr($schedule->end_time, 0, 5),
            'capacity' => $schedule->capacity,
            'filled' => $schedule->activeParticipantsCount(),
            'remaining' => $schedule->remainingSlots(),
            'status' => $schedule->status,
            'is_finished' => $schedule->isFinished(),
            'is_cancelled' => $schedule->isCancelled(),
            'series_id' => $schedule->series_id,
            'is_recurring' => !empty($schedule->series_id),
            'streak_enabled' => (bool) $schedule->streak_enabled,
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
        $levelProgress = $user instanceof User ? $levelService->progress($user) : null;
        $announcements = $user instanceof User
            ? $user->userAnnouncements()
                ->whereIn('type', ['event_reminder', 'schedule_reminder'])
                ->latest()
                ->limit(6)
                ->get()
            : collect();

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
            'levelProgress',
            'announcements',
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
            'joinedScheduleIds'
        ));
    }

    /**
     * Dapatkan data ketersediaan slot terkini dari suatu jadwal (JSON).
     */
    public function scheduleAvailability(Schedule $schedule)
    {
        if (!$schedule->is_active || $schedule->isCancelled()) {
            return response()->json([
                'message' => 'Jadwal tidak aktif atau sudah dibatalkan.',
            ], 404);
        }

        $registeredCount = $schedule->activeParticipantsCount();
        $remaining = $schedule->remainingSlots();

        return response()->json([
            'registered_count' => $registeredCount,
            'remaining' => $remaining,
            'capacity' => $schedule->capacity,
            'status' => $schedule->status,
            'is_full' => $schedule->capacity !== null && $registeredCount >= $schedule->capacity,
        ]);
    }
}
