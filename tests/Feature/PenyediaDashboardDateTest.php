<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PenyediaDashboardDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_actual_dates_and_excludes_past_schedules(): void
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        Carbon::setTestNow(Carbon::parse('2026-11-14 10:00:00', $tz));

        $user = User::factory()->create([
            'role' => 'community_owner',
        ]);

        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Komunitas Lari Sehat',
            'slug' => 'komunitas-lari-sehat',
            'sport_category' => 'running',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        // 1. Past schedule (yesterday): Should NOT appear
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-13',
            'title' => 'Lari Kemarin (Lampau)',
            'day_of_week' => 'friday',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'status' => 'open',
            'is_active' => true,
        ]);

        // 2. Schedule earlier today that already finished: Should NOT appear
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-14',
            'title' => 'Lari Pagi Tadi (Selesai)',
            'day_of_week' => 'saturday',
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'status' => 'open',
            'is_active' => true,
        ]);

        // 3. Schedule later today (upcoming/active, recurring): Should appear
        $recurringSeriesId = (string) Str::uuid();
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-14',
            'title' => 'Lari Sore Hari Ini',
            'day_of_week' => 'saturday',
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'status' => 'open',
            'series_id' => $recurringSeriesId,
            'is_recurring' => true,
            'is_active' => true,
        ]);

        // 4. Future schedule (next week Friday, one-time): Should appear with actual date 20 Nov
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-20',
            'title' => 'Lari Jumat Depan',
            'day_of_week' => 'friday',
            'start_time' => '06:30:00',
            'end_time' => '08:00:00',
            'status' => 'open',
            'series_id' => null,
            'is_recurring' => false,
            'is_active' => true,
        ]);

        // 5. Cancelled schedule in the future: Should NOT appear
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-21',
            'title' => 'Lari Dibatalkan',
            'day_of_week' => 'saturday',
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now($tz),
            'is_active' => true,
        ]);

        // 6. Inactive schedule in the future: Should NOT appear
        Schedule::create([
            'community_id' => $community->id,
            'date' => '2026-11-22',
            'title' => 'Lari Nonaktif',
            'day_of_week' => 'sunday',
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
            'status' => 'open',
            'is_active' => false,
        ]);

        // 7. Future Event (between the two upcoming schedules): Should appear and be sorted
        Event::create([
            'community_id' => $community->id,
            'title' => 'Event 5K Fun Run',
            'slug' => 'event-5k-fun-run',
            'event_date' => '2026-11-18',
            'start_time' => '06:00:00',
            'end_time' => '10:00:00',
            'location' => 'GBK Senayan',
            'status' => 'published',
            'type' => 'fun_match',
            'is_active' => true,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('penyedia-komunitas.dashboard'));

        // Assert
        $response->assertStatus(200);

        $upcomingItems = $response->viewData('upcomingItems');
        $this->assertCount(3, $upcomingItems, 'Hanya 3 agenda mendatang yang seharusnya tampil');

        $titles = $upcomingItems->pluck('title')->all();
        $this->assertEquals([
            'Lari Sore Hari Ini',
            'Event 5K Fun Run',
            'Lari Jumat Depan',
        ], $titles, 'Agenda harus diurutkan secara kronologis');

        // Check recurring vs one-time labels
        $todayScheduleItem = $upcomingItems->firstWhere('title', 'Lari Sore Hari Ini');
        $this->assertTrue($todayScheduleItem['is_recurring']);

        $futureScheduleItem = $upcomingItems->firstWhere('title', 'Lari Jumat Depan');
        $this->assertNotNull($futureScheduleItem);
        $this->assertFalse($futureScheduleItem['is_recurring']);
        $this->assertEquals('20', $futureScheduleItem['date_day'], 'Tanggal harus membaca tanggal aktual di schedules.date');
        $this->assertEquals('Nov', $futureScheduleItem['date_month'], 'Bulan harus sesuai tanggal aktual');
        $this->assertStringContainsString('Jumat', $futureScheduleItem['time_label'], 'Label hari harus dalam bahasa Indonesia');
        $this->assertStringContainsString('WIB', $futureScheduleItem['time_label'], 'Timezone harus WIB');

        // Verify HTML output contains actual date and recurring labels
        $response->assertSee('20');
        $response->assertSee('Nov');
        $response->assertSee('Lari Jumat Depan');
        $response->assertSee('Lari Sore Hari Ini');
        $response->assertSee('(Rutin)');
        $response->assertSee('(Sesi)');
        $response->assertDontSee('Lari Kemarin (Lampau)');
        $response->assertDontSee('Lari Pagi Tadi (Selesai)');
        $response->assertDontSee('Lari Dibatalkan');
        $response->assertDontSee('Lari Nonaktif');

        Carbon::setTestNow();
    }
}
