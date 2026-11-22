<?php

namespace App\Services;

use App\Exceptions\CheckinException;
use App\Models\Attendance;
use App\Models\CommunityBlock;
use App\Models\Participant;
use App\Models\PointsHistory;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected StreakService $streakService
    ) {}

    /**
     * Memproses check-in peserta pada sesi jadwal.
     * Dilakukan dalam satu DB::transaction dengan lockForUpdate untuk mencegah race condition.
     *
     * @param Participant $participant
     * @param User $actor (Penyedia yang memindai / memverifikasi)
     * @param string $method ('qr_scan' atau 'manual')
     * @return array
     * @throws CheckinException
     */
    public function checkIn(Participant $participant, User $actor, string $method): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);

        return DB::transaction(function () use ($participant, $actor, $method, $tz, $now) {
            // 1. Lock participant dan verifikasi status & tipe
            $p = Participant::where('id', $participant->id)->lockForUpdate()->first();
            if (!$p) {
                throw new CheckinException('not_registered', 'Data pendaftaran peserta tidak ditemukan.');
            }

            if ($p->participatable_type !== Schedule::class) {
                throw new CheckinException('wrong_session', 'Check-in ini hanya berlaku untuk sesi jadwal komunitas.');
            }

            if ($p->status === 'attended') {
                $att = Attendance::where('participant_id', $p->id)->first();
                $timeStr = $att && $att->check_in_at ? $att->check_in_at->timezone($tz)->format('H:i') : '';
                throw new CheckinException(
                    'already_checked_in',
                    'Peserta sudah melakukan check-in' . ($timeStr ? " pada pukul {$timeStr} WIB." : '.')
                );
            }

            if ($p->status !== 'registered') {
                throw new CheckinException(
                    'not_registered',
                    "Status peserta adalah '{$p->statusLabel()}'. Hanya peserta terdaftar yang dapat check-in."
                );
            }

            // Lock Schedule dan periksa ketersediaan sesi
            $schedule = Schedule::where('id', $p->participatable_id)->lockForUpdate()->first();
            if (!$schedule) {
                throw new CheckinException('wrong_session', 'Jadwal sesi tidak ditemukan.');
            }

            if ($schedule->isCancelled() || $schedule->status === 'completed' || $schedule->cancelled_at !== null) {
                throw new CheckinException('session_cancelled', 'Sesi jadwal ini telah dibatalkan atau telah selesai.');
            }

            if (!$schedule->is_active) {
                throw new CheckinException('session_cancelled', 'Sesi jadwal ini sedang dinonaktifkan.');
            }

            $community = $schedule->community;
            if (!$community || !$community->is_active) {
                throw new CheckinException('forbidden', 'Komunitas penyelenggara jadwal sedang dinonaktifkan.');
            }

            // 2. Periksa tanggal hari ini dan jendela waktu check-in
            $todayStr = $now->toDateString();
            $scheduleDateStr = $schedule->date instanceof Carbon ? $schedule->date->format('Y-m-d') : (string) $schedule->date;

            if ($scheduleDateStr !== $todayStr) {
                $formattedDate = Carbon::parse($scheduleDateStr, $tz)->locale('id')->isoFormat('D MMMM Y');
                throw new CheckinException('outside_window', "Sesi jadwal ini dijadwalkan pada {$formattedDate}, bukan hari ini.");
            }

            $windowBeforeMinutes = (int) config('schedules.checkin_window_before_minutes', 30);
            $windowStart = $schedule->startsAt()->subMinutes($windowBeforeMinutes);
            $windowEnd = $schedule->endsAt();

            if ($now->lt($windowStart)) {
                $openTime = $windowStart->format('H:i');
                throw new CheckinException('outside_window', "Check-in belum dibuka. Check-in dibuka mulai pukul {$openTime} WIB (30 menit sebelum sesi dimulai).");
            }

            if ($now->gt($windowEnd)) {
                $endTime = $windowEnd->format('H:i');
                throw new CheckinException('outside_window', "Sesi telah berakhir pada pukul {$endTime} WIB. Check-in sudah ditutup.");
            }

            // 3. Periksa user peserta aktif & tidak diblokir di komunitas
            $user = User::where('id', $p->user_id)->lockForUpdate()->first();
            if (!$user || !$user->is_active) {
                throw new CheckinException('forbidden', 'Akun peserta tidak aktif.');
            }

            $isBlocked = CommunityBlock::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($isBlocked) {
                throw new CheckinException('blocked', "Peserta telah diblokir dari komunitas {$community->name}.");
            }

            // 4. Periksa apakah sudah ada attendance untuk participant ini
            $existingAttendance = Attendance::where('participant_id', $p->id)->first();
            if ($existingAttendance) {
                $timeStr = $existingAttendance->check_in_at ? $existingAttendance->check_in_at->timezone($tz)->format('H:i') : '';
                throw new CheckinException(
                    'already_checked_in',
                    'Peserta sudah melakukan check-in' . ($timeStr ? " pada pukul {$timeStr} WIB." : '.')
                );
            }

            // 5. Catat attendance dan update status participant
            try {
                $attendance = Attendance::create([
                    'participant_id' => $p->id,
                    'marked_by' => $actor->id,
                    'check_in_at' => $now,
                    'method' => in_array($method, ['qr_scan', 'manual'], true) ? $method : 'manual',
                ]);
            } catch (QueryException $e) {
                $existing = Attendance::where('participant_id', $p->id)->first();
                $timeStr = $existing?->check_in_at ? $existing->check_in_at->timezone($tz)->format('H:i') : '';
                throw new CheckinException(
                    'already_checked_in',
                    'Peserta sudah melakukan check-in' . ($timeStr ? " pada pukul {$timeStr} WIB." : '.')
                );
            }

            $p->status = 'attended';
            $p->save();

            // 6. Poin dan streak HANYA jika komunitas verified & schedule streak_enabled
            $pointsAwarded = false;
            $pointsAmount = 0;
            $currentStreak = 0;

            $isVerified = ($community->verification_status === 'verified');
            $isStreakEnabled = (bool) $schedule->streak_enabled;

            if ($isVerified && $isStreakEnabled) {
                $attendancePoints = (int) config('schedules.attendance_points', 30);
                $newBalance = ((int) $user->total_points) + $attendancePoints;
                $user->total_points = $newBalance;
                $user->save();

                $pointsHistory = new PointsHistory([
                    'user_id' => $user->id,
                    'points' => $attendancePoints,
                    'source' => 'attendance',
                    'description' => "Check-in sesi jadwal '{$schedule->title}' (+{$attendancePoints} Poin)",
                    'balance_after' => $newBalance,
                ]);
                $pointsHistory->pointable()->associate($schedule);
                $pointsHistory->save();

                $streak = $this->streakService->recordWeeklyAttendance($user, $community->sport_category);
                $currentStreak = (int) $streak->current_streak;

                $pointsAwarded = true;
                $pointsAmount = $attendancePoints;
            } else {
                $existingStreak = $user->streaks()
                    ->where('sport_category', $community->sport_category ?? 'Kebugaran')
                    ->first();
                $currentStreak = $existingStreak ? (int) $existingStreak->current_streak : 0;
            }

            return [
                'user' => $user,
                'participant' => $p,
                'attendance' => $attendance,
                'check_in_at' => $attendance->check_in_at,
                'check_in_at_formatted' => $now->format('H:i'),
                'points_awarded' => $pointsAwarded,
                'points_amount' => $pointsAmount,
                'current_streak' => $currentStreak,
                'total_points' => (int) $user->total_points,
                'method' => $attendance->method,
            ];
        });
    }
}

