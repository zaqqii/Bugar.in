<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\UserAnnouncement;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUpcomingActivityReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TIMEZONE = 'Asia/Jakarta';

    public function handle(): void
    {
        $now = Carbon::now(self::TIMEZONE);
        $windowEnd = $now->copy()->addDay();

        Participant::query()
            ->with(['user', 'participatable.community'])
            ->whereIn('status', ['registered', 'waiting_list'])
            ->whereIn('participatable_type', [Event::class, Schedule::class])
            ->chunkById(200, function ($participants) use ($now, $windowEnd): void {
                foreach ($participants as $participant) {
                    $activity = $participant->participatable;

                    if (! $activity || ! $participant->user) {
                        continue;
                    }

                    if ($activity instanceof Event) {
                        if (! $activity->is_active || ! in_array($activity->status, ['published', 'registration_closed', 'ongoing'], true)) {
                            continue;
                        }

                        $occurrence = Carbon::parse(
                            $activity->event_date->toDateString() . ' ' . $activity->start_time,
                            self::TIMEZONE,
                        );
                        $type = 'event_reminder';
                        $activityLabel = 'Event';
                    } elseif ($activity instanceof Schedule) {
                        if (! $activity->is_active || in_array($activity->status, ['cancelled', 'completed'], true)) {
                            continue;
                        }

                        $occurrence = $this->nextScheduleOccurrence($activity, $now);
                        $type = 'schedule_reminder';
                        $activityLabel = 'Jadwal olahraga';
                    } else {
                        continue;
                    }

                    if ($occurrence->lt($now) || $occurrence->gt($windowEnd)) {
                        continue;
                    }

                    $this->createReminder($participant, $activity, $type, $activityLabel, $occurrence);
                }
            });

        UserAnnouncement::query()
            ->whereIn('type', ['event_reminder', 'schedule_reminder'])
            ->whereDate('created_at', '<', $now->copy()->subDays(30)->toDateString())
            ->delete();
    }

    private function nextScheduleOccurrence(Schedule $schedule, Carbon $now): Carbon
    {
        $weekday = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ][strtolower(trim($schedule->day_of_week))] ?? Carbon::MONDAY;

        $daysUntil = ($weekday - $now->dayOfWeek + 7) % 7;
        $occurrence = $now->copy()->startOfDay()->addDays($daysUntil);
        [$hour, $minute, $second] = array_pad(array_map('intval', explode(':', $schedule->start_time)), 3, 0);
        $occurrence->setTime($hour, $minute, $second);

        if ($occurrence->lt($now)) {
            $occurrence->addWeek();
        }

        return $occurrence;
    }

    private function createReminder(
        Participant $participant,
        Event|Schedule $activity,
        string $type,
        string $activityLabel,
        Carbon $occurrence,
    ): void {
        $occurrenceDate = $occurrence->toDateString();
        $alreadyExists = UserAnnouncement::query()
            ->where('user_id', $participant->user_id)
            ->where('type', $type)
            ->where('notifiable_type', $activity::class)
            ->where('notifiable_id', $activity->id)
            ->where('data->occurrence_date', $occurrenceDate)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $communityName = $activity->community?->name;
        $dateLabel = $occurrence->locale('id')->translatedFormat('l, j F');
        $timeLabel = $occurrence->format('H:i');
        $body = "{$activityLabel} {$activity->title} akan dilaksanakan {$dateLabel} pukul {$timeLabel} WIB";

        if ($communityName) {
            $body .= " bersama {$communityName}";
        }

        if ($activity->location) {
            $body .= " di {$activity->location}";
        }

        $body .= '. Jangan lupa hadir sesuai waktu yang ditentukan.';

        UserAnnouncement::create([
            'user_id' => $participant->user_id,
            'type' => $type,
            'title' => "Pengingat {$activityLabel}",
            'body' => $body,
            'data' => [
                'activity_title' => $activity->title,
                'community_name' => $communityName,
                'occurrence_date' => $occurrenceDate,
                'occurrence_at' => $occurrence->toIso8601String(),
            ],
            'notifiable_type' => $activity::class,
            'notifiable_id' => $activity->id,
        ]);
    }
}
