<?php

namespace Tests\Feature;

use App\Exceptions\CheckinException;
use App\Http\Controllers\PenyediaKomunitas\EventController;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ManualAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        // Daftarkan route pengujian untuk EventController aksi manual absensi
        $this->app['router']->post('/test-provider/events/{event}/checkin/manual/{participant}', [EventController::class, 'checkinManual'])
            ->middleware(['web', 'auth', 'community_owner']);
        $this->app['router']->post('/test-provider/events/{event}/mark-no-show/{participant}', [EventController::class, 'markNoShow'])
            ->middleware(['web', 'auth', 'community_owner']);
    }

    /**
     * Helper untuk membuat User
     */
    protected function createUser(array $attrs = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'community_owner',
            'total_points' => 0,
            'is_active' => true,
        ], $attrs));
    }

    /**
     * Helper untuk membuat Community
     */
    protected function createCommunity(User $owner, array $attrs = []): Community
    {
        return Community::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'Komunitas Bugar Test',
            'slug' => 'komunitas-bugar-test-' . uniqid(),
            'sport_category' => 'running',
            'verification_status' => 'verified',
            'is_active' => true,
        ], $attrs));
    }

    /**
     * Helper untuk membuat Schedule
     */
    protected function createSchedule(Community $community, array $attrs = []): Schedule
    {
        $today = Carbon::now('Asia/Jakarta');
        return Schedule::create(array_merge([
            'community_id' => $community->id,
            'title' => 'Lari Pagi Bersama',
            'date' => $today->toDateString(),
            'day_of_week' => strtolower($today->englishDayOfWeek),
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'capacity' => 20,
            'status' => 'open',
            'is_active' => true,
            'streak_enabled' => true,
        ], $attrs));
    }

    /**
     * Helper untuk membuat Event
     */
    protected function createEvent(Community $community, array $attrs = []): Event
    {
        $today = Carbon::now('Asia/Jakarta');
        return Event::create(array_merge([
            'community_id' => $community->id,
            'title' => 'Event Lari 5K',
            'slug' => 'event-lari-5k-' . uniqid(),
            'event_date' => $today->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '10:00:00',
            'location' => 'GBK Senayan',
            'capacity' => 50,
            'registration_fee' => 0,
            'status' => 'published',
            'is_active' => true,
        ], $attrs));
    }

    /**
     * 1. Penyedia pemilik berhasil menandai hadir peserta via AttendanceService (Jadwal).
     */
    public function test_owner_provider_can_mark_attendance_via_service(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $schedule = $this->createSchedule($community);

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $schedule->id,
            'status' => 'registered',
        ]);

        // Mock AttendanceService
        $mockAttendanceService = Mockery::mock(AttendanceService::class);
        $mockAttendanceService->shouldReceive('checkIn')
            ->once()
            ->with(Mockery::on(fn($p) => $p->id === $participant->id), Mockery::on(fn($u) => $u->id === $owner->id), 'manual')
            ->andReturn([
                'user' => $participantUser,
                'participant' => $participant,
                'attendance' => new Attendance(['participant_id' => $participant->id, 'marked_by' => $owner->id]),
                'check_in_at' => Carbon::now(),
                'check_in_at_formatted' => '07:15',
                'points_awarded' => true,
                'points_amount' => 30,
                'current_streak' => 2,
                'total_points' => 30,
                'method' => 'manual',
            ]);

        $this->app->instance(AttendanceService::class, $mockAttendanceService);

        $response = $this->actingAs($owner)
            ->postJson(route('penyedia-komunitas.jadwal.checkin.manual', [$schedule, $participant]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'points_awarded' => true,
                'points_amount' => 30,
            ]);
    }

    /**
     * 2. Penyedia lain (akses silang antar-komunitas) ditolak dengan 403 Forbidden.
     */
    public function test_other_provider_is_forbidden_to_mark_attendance(): void
    {
        $ownerA = $this->createUser(['email' => 'ownera@example.com']);
        $communityA = $this->createCommunity($ownerA);
        $scheduleA = $this->createSchedule($communityA);

        $ownerB = $this->createUser(['email' => 'ownerb@example.com']); // Penyedia komunitas lain

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $scheduleA->id,
            'status' => 'registered',
        ]);

        $response = $this->actingAs($ownerB)
            ->postJson(route('penyedia-komunitas.jadwal.checkin.manual', [$scheduleA, $participant]));

        $response->assertStatus(403);
    }

    /**
     * 3. Peserta yang tidak eligible (misal salah sesi) ditolak.
     */
    public function test_ineligible_participant_is_rejected(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $schedule = $this->createSchedule($community);

        $otherSchedule = $this->createSchedule($community, ['title' => 'Sesi Lain']);

        $participantUser = $this->createUser(['role' => 'user']);
        // Peserta terdaftar di otherSchedule, bukan schedule yang diperiksa
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $otherSchedule->id,
            'status' => 'registered',
        ]);

        $response = $this->actingAs($owner)
            ->postJson(route('penyedia-komunitas.jadwal.checkin.manual', [$schedule, $participant]));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'wrong_session',
            ]);
    }

    /**
     * 4. Aksi ganda tidak membuat data ganda (penanganan already_checked_in exception).
     */
    public function test_duplicate_attendance_action_is_handled_gracefully(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $schedule = $this->createSchedule($community);

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $schedule->id,
            'status' => 'attended',
        ]);

        // Mock AttendanceService melempar CheckinException already_checked_in
        $mockAttendanceService = Mockery::mock(AttendanceService::class);
        $mockAttendanceService->shouldReceive('checkIn')
            ->once()
            ->andThrow(new CheckinException('already_checked_in', 'Peserta sudah melakukan check-in pada pukul 07:00 WIB.'));

        $this->app->instance(AttendanceService::class, $mockAttendanceService);

        $response = $this->actingAs($owner)
            ->postJson(route('penyedia-komunitas.jadwal.checkin.manual', [$schedule, $participant]));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'already_checked_in',
            ]);
    }

    /**
     * 5. Pesan feedback tampil sesuai pada respons web / redirect.
     */
    public function test_feedback_message_in_web_redirect(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $schedule = $this->createSchedule($community);

        $participantUser = $this->createUser(['role' => 'user', 'name' => 'Budi Santoso']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $schedule->id,
            'status' => 'registered',
        ]);

        $mockAttendanceService = Mockery::mock(AttendanceService::class);
        $mockAttendanceService->shouldReceive('checkIn')
            ->once()
            ->andReturn([
                'user' => $participantUser,
                'participant' => $participant,
                'attendance' => new Attendance(['participant_id' => $participant->id, 'marked_by' => $owner->id]),
                'check_in_at' => Carbon::now(),
                'check_in_at_formatted' => '07:20',
                'points_awarded' => true,
                'points_amount' => 30,
                'current_streak' => 1,
                'total_points' => 30,
                'method' => 'manual',
            ]);

        $this->app->instance(AttendanceService::class, $mockAttendanceService);

        $response = $this->actingAs($owner)
            ->post(route('penyedia-komunitas.jadwal.checkin.manual', [$schedule, $participant]));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Check-in manual untuk Budi Santoso berhasil dicatat! (+30 Poin)');
    }

    /**
     * 6. Event: Penyedia pemilik dapat memanggil checkinManual di EventController
     */
    public function test_event_controller_manual_checkin_success_with_service(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $event = $this->createEvent($community);

        $participantUser = $this->createUser(['role' => 'user', 'name' => 'Ani Wijaya']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Event::class,
            'participatable_id' => $event->id,
            'status' => 'registered',
        ]);

        $mockAttendanceService = Mockery::mock(AttendanceService::class);
        $mockAttendanceService->shouldReceive('checkIn')
            ->once()
            ->with(Mockery::on(fn($p) => $p->id === $participant->id), Mockery::on(fn($u) => $u->id === $owner->id), 'manual')
            ->andReturn([
                'user' => $participantUser,
                'participant' => $participant,
                'attendance' => new Attendance(['participant_id' => $participant->id, 'marked_by' => $owner->id]),
                'check_in_at' => Carbon::now(),
                'check_in_at_formatted' => '08:00',
                'points_awarded' => false,
                'points_amount' => 0,
                'total_points' => 0,
                'method' => 'manual',
            ]);

        $this->app->instance(AttendanceService::class, $mockAttendanceService);

        $response = $this->actingAs($owner)
            ->postJson("/test-provider/events/{$event->id}/checkin/manual/{$participant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Check-in manual untuk Ani Wijaya berhasil.',
            ]);
    }

    /**
     * 7. Event: Penyedia lain dilarang (403) mengakses absensi event komunitas lain.
     */
    public function test_event_controller_denies_unauthorized_provider(): void
    {
        $ownerA = $this->createUser(['email' => 'owner_a@example.com']);
        $communityA = $this->createCommunity($ownerA);
        $eventA = $this->createEvent($communityA);

        $ownerB = $this->createUser(['email' => 'owner_b@example.com']);

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Event::class,
            'participatable_id' => $eventA->id,
            'status' => 'registered',
        ]);

        $response = $this->actingAs($ownerB)
            ->postJson("/test-provider/events/{$eventA->id}/checkin/manual/{$participant->id}");

        $response->assertStatus(403);
    }

    /**
     * 8. Event: Peserta cancelled ditolak oleh EventController.
     */
    public function test_event_controller_rejects_cancelled_participant(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $event = $this->createEvent($community);

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Event::class,
            'participatable_id' => $event->id,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($owner)
            ->postJson("/test-provider/events/{$event->id}/checkin/manual/{$participant->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'not_registered',
            ]);
    }

    /**
     * 9. Event: markNoShow mengembalikan status 501 jika AttendanceService belum menyediakan method.
     */
    public function test_event_controller_mark_no_show_handles_unimplemented_service(): void
    {
        $owner = $this->createUser();
        $community = $this->createCommunity($owner);
        $event = $this->createEvent($community);

        $participantUser = $this->createUser(['role' => 'user']);
        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participatable_type' => Event::class,
            'participatable_id' => $event->id,
            'status' => 'registered',
        ]);

        $response = $this->actingAs($owner)
            ->postJson("/test-provider/events/{$event->id}/mark-no-show/{$participant->id}");

        $response->assertStatus(501)
            ->assertJson([
                'success' => false,
                'error_code' => 'not_implemented',
            ]);
    }
}
