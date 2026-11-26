<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProviderActivityOverlap
{
    /**
     * Find same-community activities whose time overlaps the proposed activity.
     * Same-location overlaps block saving; other locations are returned as warnings.
     *
     * @return array{blocking: array<int, string>, warnings: array<int, string>}
     */
    public function inspect(
        int $communityId,
        string $date,
        string $startTime,
        ?string $endTime,
        string $location,
        ?int $ignoreScheduleId = null,
        ?int $ignoreEventId = null,
    ): array {
        $activities = collect();

        $schedules = Schedule::query()
            ->where('community_id', $communityId)
            ->whereDate('date', $date)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->when($ignoreScheduleId, fn (Builder $query) => $query->where('id', '!=', $ignoreScheduleId))
            ->get(['id', 'title', 'location', 'start_time', 'end_time'])
            ->map(fn (Schedule $schedule) => [
                'kind' => 'Jadwal',
                'title' => $schedule->title,
                'location' => $schedule->location,
                'start' => $schedule->start_time,
                'end' => $schedule->end_time,
            ]);

        $events = Event::query()
            ->where('community_id', $communityId)
            ->whereDate('event_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->when($ignoreEventId, fn (Builder $query) => $query->where('id', '!=', $ignoreEventId))
            ->get(['id', 'title', 'location', 'start_time', 'end_time'])
            ->map(fn (Event $event) => [
                'kind' => 'Event',
                'title' => $event->title,
                'location' => $event->location,
                'start' => $event->start_time,
                'end' => $event->end_time,
            ]);

        $blocking = [];
        $warnings = [];
        $proposedLocation = $this->normalizeLocation($location);

        foreach ($schedules->concat($events) as $activity) {
            if (! $this->overlaps($startTime, $endTime, $activity['start'], $activity['end'])) {
                continue;
            }

            $start = substr($activity['start'], 0, 5);
            $end = $activity['end'] ? substr($activity['end'], 0, 5) : 'jam selesai belum diatur';
            $message = "{$activity['kind']} \"{$activity['title']}\" ({$start}–{$end}) pada tanggal {$date}";

            if ($proposedLocation === $this->normalizeLocation((string) $activity['location'])) {
                $blocking[] = "Bentrok dengan {$message} di lokasi yang sama.";
            } else {
                $warnings[] = "Waktu kegiatan beririsan dengan {$message}, tetapi lokasinya berbeda.";
            }
        }

        return ['blocking' => $blocking, 'warnings' => $warnings];
    }

    private function overlaps(string $startA, ?string $endA, string $startB, ?string $endB): bool
    {
        $startA = substr($startA, 0, 8);
        $startB = substr($startB, 0, 8);

        if ($endA && $endB) {
            return $startA < substr($endB, 0, 8) && $startB < substr($endA, 0, 8);
        }

        // An event without an end time is treated as a point at its start time.
        if (! $endA && ! $endB) {
            return $startA === $startB;
        }

        if (! $endA) {
            return $startA >= $startB && $startA < substr((string) $endB, 0, 8);
        }

        return $startB >= $startA && $startB < substr($endA, 0, 8);
    }

    private function normalizeLocation(string $location): string
    {
        return Str::lower((string) preg_replace('/\s+/u', ' ', trim($location)));
    }
}
