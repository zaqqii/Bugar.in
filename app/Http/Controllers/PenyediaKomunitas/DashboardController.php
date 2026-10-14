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

        $upcomingEvents = $community->events()
            ->whereIn('status', ['published', 'registration_closed', 'ongoing'])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(fn(Event $event) => $this->formatEventRow($event));

        $upcomingSchedules = $community->schedules()
            ->whereIn('status', ['open', 'almost_full'])
            ->where('is_active', true)
            ->orderByRaw("CASE day_of_week
                WHEN 'monday'    THEN 1
                WHEN 'tuesday'   THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday'  THEN 4
                WHEN 'friday'    THEN 5
                WHEN 'saturday'  THEN 6
                WHEN 'sunday'    THEN 7
                END")
            ->limit(3)
            ->get()
            ->map(fn(Schedule $schedule) => $this->formatScheduleRow($schedule));

        $upcomingItems = $upcomingEvents->concat($upcomingSchedules)->take(5);

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
        $filled = $event->participants_count;
        $capacity = $event->capacity;
        $isFull = $capacity !== null && $filled >= $capacity;
        $percentage = $event->fillPercentage();
        $date = Carbon::parse($event->event_date);

        return [
            'type' => 'event',
            'date_month' => $date->isoFormat('MMM'),
            'date_day' => $date->format('d'),
            'title' => $event->title,
            'time_label' => $date->translatedFormat('l') . ', ' . substr($event->start_time, 0, 5) . ' WIB',
            'location' => $event->location,
            'slots_filled' => $filled,
            'slots_total' => $capacity ?? '∞',
            'percentage' => $percentage . '%',
            'is_full' => $isFull,
            'status_label' => self::EVENT_STATUS_LABELS[$event->status] ?? $event->status,
            'status_color' => $isFull ? 'var(--color-gray-200)' : 'var(--color-primary-100)',
            'status_dot_color' => $isFull ? '#94a3b8' : 'var(--color-primary-500)',
        ];
    }

    private function formatScheduleRow(Schedule $schedule): array
    {
        $filled = $schedule->filledCount();
        $capacity = $schedule->capacity;
        $isFull = $capacity !== null && $filled >= $capacity;
        $percentage = $schedule->fillPercentage();
        $nextDate = $this->nextOccurrence($schedule->day_of_week);

        return [
            'type' => 'schedule',
            'date_month' => $nextDate->isoFormat('MMM'),
            'date_day' => $nextDate->format('d'),
            'title' => $schedule->title,
            'time_label' => $schedule->dayLabel() . ', ' . substr($schedule->start_time, 0, 5) . ' WIB',
            'location' => $schedule->location ?? '-',
            'slots_filled' => $filled,
            'slots_total' => $capacity ?? '∞',
            'percentage' => $percentage . '%',
            'is_full' => $isFull,
            'status_label' => self::SCHEDULE_STATUS_LABELS[$schedule->status] ?? $schedule->status,
            'status_color' => $isFull ? 'var(--color-gray-200)' : 'var(--color-primary-100)',
            'status_dot_color' => $isFull ? '#94a3b8' : 'var(--color-primary-500)',
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

        $targetDow = $map[$dayOfWeek] ?? Carbon::MONDAY;
        $today = now()->startOfDay();

        return $today->dayOfWeek === $targetDow ? $today : $today->next($targetDow);
    }
}