<?php

namespace App\Http\Controllers\PenyediaKomunitas;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const EVENT_STATUS_LABELS = [
        'draft' => 'Draft',
        'published' => 'Pendaftaran Dibuka',
        'registration_closed' => 'Pendaftaran Ditutup',
        'ongoing' => 'Sedang Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    private const SCHEDULE_STATUS_LABELS = [
        'open' => 'Buka',
        'almost_full' => 'Hampir Penuh',
        'full' => 'Penuh',
        'cancelled' => 'Dibatalkan',
        'completed' => 'Selesai',
    ];

    public function index()
    {
        $user = Auth::user();

        $community = Community::where('owner_id', $user->id)
            ->withCount([
                'members as active_members_count' => fn($q) => $q->where('status', 'active'),
                'members as pending_members_count' => fn($q) => $q->where('status', 'pending'),
            ])
            ->first();

        if (!$community) {
            return view('penyedia-komunitas.dashboard', [
                'community' => null,
                'stats' => null,
                'upcomingItems' => collect(),
                'pendingMembers' => collect(),
            ]);
        }

        $activeParticipantsThisMonth = Participant::whereHasMorph(
            'participatable',
            [Event::class],
            fn($q) => $q->where('community_id', $community->id)
        )
            ->whereYear('registered_at', now()->year)
            ->whereMonth('registered_at', now()->month)
            ->whereIn('status', ['registered', 'attended'])
            ->count();

        $totalActiveEvents = $community->events()
            ->whereIn('status', ['published', 'registration_closed', 'ongoing'])
            ->count();

        $stats = [
            'active_members' => $community->active_members_count ?? 0,
            'followers' => $community->followers_count,
            'active_participants_month' => $activeParticipantsThisMonth,
            'total_active_events' => $totalActiveEvents,
            'average_rating' => $community->average_rating,
            'ratings_count' => $community->ratings_count,
        ];

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $todayStr = $now->toDateString();
        $nowTimeStr = $now->format('H:i:s');

        $upcomingEvents = $community->events()
            ->whereIn('status', ['published', 'registration_closed', 'ongoing'])
            ->where(function ($q) use ($todayStr, $nowTimeStr) {
                $q->whereDate('event_date', '>', $todayStr)
                  ->orWhere(function ($sub) use ($todayStr, $nowTimeStr) {
                      $sub->whereDate('event_date', '=', $todayStr)
                          ->where(function ($timeSub) use ($nowTimeStr) {
                              $timeSub->where('status', 'ongoing')
                                  ->orWhere(function ($t) use ($nowTimeStr) {
                                      $t->whereNotNull('end_time')
                                        ->where('end_time', '>=', $nowTimeStr);
                                  })
                                  ->orWhere(function ($t) use ($nowTimeStr) {
                                      $t->whereNull('end_time')
                                        ->where('start_time', '>=', $nowTimeStr);
                                  });
                          });
                  });
            })
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get()
            ->map(fn(Event $event) => $this->formatEventRow($event));

        $upcomingSchedules = $community->schedules()
            ->where('is_active', true)
            ->whereNull('cancelled_at')
            ->whereIn('status', ['open', 'almost_full', 'full'])
            ->where(function ($q) use ($todayStr, $nowTimeStr) {
                $q->whereDate('date', '>', $todayStr)
                  ->orWhere(function ($sub) use ($todayStr, $nowTimeStr) {
                      $sub->whereDate('date', '=', $todayStr)
                          ->where('end_time', '>=', $nowTimeStr);
                  });
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get()
            ->map(fn(Schedule $schedule) => $this->formatScheduleRow($schedule));

        $upcomingItems = $upcomingEvents->concat($upcomingSchedules)
            ->sortBy('raw_datetime')
            ->values()
            ->take(5);

        $pendingMembers = $community->members()
            ->with('user')
            ->where('status', 'pending')
            ->orderBy('joined_at')
            ->limit(5)
            ->get();

        return view('penyedia-komunitas.dashboard', compact(
            'community',
            'stats',
            'upcomingItems',
            'pendingMembers'
        ));
    }

    private function formatEventRow(Event $event): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $filled = $event->participants_count;
        $capacity = $event->capacity;
        $isFull = $capacity !== null && $filled >= $capacity;
        $percentage = $event->fillPercentage();
        $date = Carbon::parse($event->event_date, $tz)->locale('id');
        $startTime = substr($event->start_time, 0, 5);
        $rawDatetime = Carbon::parse($date->format('Y-m-d') . ' ' . $event->start_time, $tz);

        return [
            'type' => 'event',
            'raw_datetime' => $rawDatetime,
            'date_month' => $date->isoFormat('MMM'),
            'date_day' => $date->format('d'),
            'title' => $event->title,
            'time_label' => $date->isoFormat('dddd') . ', ' . $startTime . ' WIB',
            'location' => $event->location,
            'slots_filled' => $filled,
            'slots_total' => $capacity ?? '∞',
            'percentage' => $percentage . '%',
            'is_full' => $isFull,
            'status_label' => self::EVENT_STATUS_LABELS[$event->status] ?? $event->status,
            'status_color' => $isFull ? 'var(--color-gray-200)' : 'var(--color-primary-100)',
            'status_dot_color' => $isFull ? '#94a3b8' : 'var(--color-primary-500)',
            'action_url' => route('penyedia-komunitas.events.edit', $event),
            'is_recurring' => false,
        ];
    }

    private function formatScheduleRow(Schedule $schedule): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $filled = $schedule->filledCount();
        $capacity = $schedule->capacity;
        $isFull = $capacity !== null && $filled >= $capacity;
        $percentage = $schedule->fillPercentage();

        $date = $schedule->date
            ? Carbon::parse($schedule->date, $tz)->locale('id')
            : $this->nextOccurrence($schedule->day_of_week)->locale('id');

        $startTime = substr($schedule->start_time, 0, 5);
        $rawDatetime = $schedule->date
            ? $schedule->startsAt()
            : $date->copy()->setTimeFromTimeString($schedule->start_time);

        return [
            'type' => 'schedule',
            'raw_datetime' => $rawDatetime,
            'date_month' => $date->isoFormat('MMM'),
            'date_day' => $date->format('d'),
            'title' => $schedule->title,
            'time_label' => $schedule->dayLabel() . ', ' . $startTime . ' WIB',
            'location' => $schedule->location ?? '-',
            'slots_filled' => $filled,
            'slots_total' => $capacity ?? '∞',
            'percentage' => $percentage . '%',
            'is_full' => $isFull,
            'status_label' => self::SCHEDULE_STATUS_LABELS[$schedule->status] ?? $schedule->status,
            'status_color' => $isFull ? 'var(--color-gray-200)' : 'var(--color-primary-100)',
            'status_dot_color' => $isFull ? '#94a3b8' : 'var(--color-primary-500)',
            'action_url' => route('penyedia-komunitas.jadwal.show', $schedule),
            'is_recurring' => (bool) $schedule->is_recurring,
        ];
    }

    private function nextOccurrence(string $dayOfWeek): Carbon
    {
        $map = [
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
        ];

        $targetDow = $map[strtolower(trim($dayOfWeek))] ?? Carbon::MONDAY;
        $tz = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();

        return $today->dayOfWeek === $targetDow ? $today : $today->next($targetDow);
    }
}