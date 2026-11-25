<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityOverlapService
{
    /**
     * Menentukan apakah dua rentang waktu saling bertabrakan (overlap).
     *
     * Rumus: bentrok jika mulaiA < selesaiB DAN mulaiB < selesaiA (interval terbuka di ujung).
     * Waktu dibandingkan sebagai instant waktu universal (UTC timestamp) sehingga
     * perbedaan zona waktu (WIB, WITA, WIT) otomatis tertangani dengan benar.
     * Selesai tepat pada waktu mulai kegiatan berikutnya (mis. 09:00 dan 09:00) TIDAK dianggap bentrok.
     *
     * Catatan untuk Tim / Zaqi (Task ZQ-06):
     * Ini adalah SATU-SATUNYA rumus standar penentuan bentrok waktu di Bugar.in.
     * Validasi sisi penyedia (ZQ-06) WAJIB menggunakan method ini agar konsisten dengan
     * validasi sisi user (RF-05).
     *
     * @param Carbon $startA Waktu mulai rentang A
     * @param Carbon $endA   Waktu selesai rentang A
     * @param Carbon $startB Waktu mulai rentang B
     * @param Carbon $endB   Waktu selesai rentang B
     * @return bool True jika bertabrakan, false jika tidak
     */
    public function overlaps(Carbon $startA, Carbon $endA, Carbon $startB, Carbon $endB): bool
    {
        return $startA->lt($endB) && $startB->lt($endA);
    }

    /**
     * Memeriksa apakah user memiliki kegiatan terdaftar lain (jadwal atau event) yang
     * waktunya bertabrakan dengan rentang waktu [$start, $end].
     *
     * Komitmen aktif user yang diperiksa adalah pendaftaran berstatus:
     * - 'registered'
     * - 'waiting_payment'
     * - 'attended'
     *
     * Kegiatan yang diabaikan:
     * - Kegiatan berstatus 'cancelled' atau jadwal dengan 'cancelled_at' terisi
     * - Kegiatan tidak aktif (is_active = false) atau soft-deleted
     * - Pendaftaran berstatus 'cancelled', 'no_show', 'waiting_list'
     * - Event tanpa event_date atau start_time (dilewati dan dicatat log)
     * - $excludeActivity (kegiatan yang sedang didaftar ulang/diedit)
     *
     * Query dibatasi ke rentang target plus-minus 1 hari untuk efisiensi dan keamanan
     * lintas zona waktu (WIB, WITA, WIT).
     *
     * @param User $user User yang diperiksa
     * @param Carbon $start Waktu mulai kegiatan target
     * @param Carbon $end Waktu selesai kegiatan target
     * @param Schedule|Event|null $excludeActivity Kegiatan yang dikecualikan dari pemeriksaan
     * @return array{type: string, jenis: string, title: string, judul: string, start: string, mulai: string, end: string, selesai: string, timezone: string, zona: string, date: string, tanggal: string, activity: Schedule|Event}|null
     */
    public function userConflict(User $user, Carbon $start, Carbon $end, $excludeActivity = null): ?array
    {
        // Batasi pencarian ke rentang tanggal target plus-minus 1 hari (memperhitungkan perbedaan zona waktu)
        $targetDateStart = $start->copy()->subDay()->toDateString();
        $targetDateEnd = $end->copy()->addDay()->toDateString();

        // 1. Cek bentrok dengan Jadwal (Schedule)
        $schedulesQuery = Schedule::whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->whereIn('status', Participant::SEAT_STATUSES);
            })
            ->whereDate('date', '>=', $targetDateStart)
            ->whereDate('date', '<=', $targetDateEnd)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->where('is_active', true);

        if ($excludeActivity instanceof Schedule) {
            $schedulesQuery->where('id', '!=', $excludeActivity->id);
        }

        $schedules = $schedulesQuery->get();

        foreach ($schedules as $schedule) {
            $scheduleStart = $schedule->startsAt();
            $scheduleEnd = $schedule->endsAt();

            if ($this->overlaps($start, $end, $scheduleStart, $scheduleEnd)) {
                $tzLabel = method_exists($schedule, 'timezoneLabel') ? $schedule->timezoneLabel() : 'WIB';
                return [
                    'type'     => 'jadwal',
                    'jenis'    => 'jadwal',
                    'title'    => $schedule->title,
                    'judul'    => $schedule->title,
                    'start'    => $scheduleStart->format('H:i'),
                    'mulai'    => $scheduleStart->format('H:i'),
                    'end'      => $scheduleEnd->format('H:i'),
                    'selesai'  => $scheduleEnd->format('H:i'),
                    'timezone' => $tzLabel,
                    'zona'     => $tzLabel,
                    'date'     => $scheduleStart->copy()->locale('id')->translatedFormat('d F Y'),
                    'tanggal'  => $scheduleStart->copy()->locale('id')->translatedFormat('d F Y'),
                    'activity' => $schedule,
                ];
            }
        }

        // 2. Cek bentrok dengan Event
        $eventsQuery = Event::whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->whereIn('status', Participant::SEAT_STATUSES);
            })
            ->whereDate('event_date', '>=', $targetDateStart)
            ->whereDate('event_date', '<=', $targetDateEnd)
            ->where('status', '!=', 'cancelled')
            ->where('is_active', true);

        if ($excludeActivity instanceof Event) {
            $eventsQuery->where('id', '!=', $excludeActivity->id);
        }

        $events = $eventsQuery->get();

        foreach ($events as $ev) {
            // Event tanpa event_date atau start_time: lewati cek dan laporkan
            if (! $ev->event_date || ! $ev->start_time) {
                Log::info("ActivityOverlapService: Event ID {$ev->id} dilewati dari pengecekan bentrok karena tidak memiliki event_date atau start_time lengkap.");
                continue;
            }

            $evStart = $ev->startsAt();
            $evEnd = method_exists($ev, 'overlapEndsAt') ? $ev->overlapEndsAt() : $ev->endsAt();

            if (! $evStart || ! $evEnd) {
                Log::info("ActivityOverlapService: Event ID {$ev->id} dilewati karena waktu mulai/selesai tidak dapat dihitung.");
                continue;
            }

            if ($this->overlaps($start, $end, $evStart, $evEnd)) {
                $tzLabel = method_exists($ev, 'timezoneLabel') ? $ev->timezoneLabel() : 'WIB';
                return [
                    'type'     => 'event',
                    'jenis'    => 'event',
                    'title'    => $ev->title,
                    'judul'    => $ev->title,
                    'start'    => $evStart->format('H:i'),
                    'mulai'    => $evStart->format('H:i'),
                    'end'      => $evEnd->format('H:i'),
                    'selesai'  => $evEnd->format('H:i'),
                    'timezone' => $tzLabel,
                    'zona'     => $tzLabel,
                    'date'     => $evStart->copy()->locale('id')->translatedFormat('d F Y'),
                    'tanggal'  => $evStart->copy()->locale('id')->translatedFormat('d F Y'),
                    'activity' => $ev,
                ];
            }
        }

        return null;
    }
}
