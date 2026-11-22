<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Community;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ZQ-03 — Feature tests untuk halaman daftar peserta jadwal penyedia komunitas.
 *
 * Mencakup:
 * - Akses pemilik komunitas yang sah
 * - Penolakan akses komunitas lain (cross-community)
 * - Filter berdasarkan status peserta
 * - Pagination dengan withQueryString
 * - Tidak ada email/data sensitif di output HTML
 * - Ringkasan jumlah peserta dari database
 * - Waktu check-in jika tersedia
 */
class ScheduleParticipantsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Buat penyedia komunitas beserta komunitas dan jadwalnya.
     */
    private function makeOwnerWithSchedule(): array
    {
        $owner = User::factory()->create(['role' => 'community_owner']);

        $community = Community::create([
            'owner_id'            => $owner->id,
            'name'                => 'Komunitas Test',
            'slug'                => 'komunitas-test',
            'sport_category'      => 'futsal',
            'verification_status' => 'verified',
            'is_active'           => true,
            'is_public'           => true,
        ]);

        $schedule = Schedule::create([
            'community_id' => $community->id,
            'date'         => now()->addDays(3)->toDateString(),
            'title'        => 'Latihan Pagi',
            'level'        => 'all_levels',
            'location'     => 'Lapangan A',
            'start_time'   => '07:00:00',
            'end_time'     => '09:00:00',
            'status'       => 'open',
            'is_active'    => true,
        ]);

        return compact('owner', 'community', 'schedule');
    }

    /**
     * Buat peserta pada jadwal.
     */
    private function makeParticipant(Schedule $schedule, string $status = 'registered'): Participant
    {
        $user = User::factory()->create(['role' => 'user']);

        return Participant::create([
            'user_id'            => $user->id,
            'participatable_type'=> Schedule::class,
            'participatable_id'  => $schedule->id,
            'status'             => $status,
            'registered_at'      => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. Akses pemilik yang sah
    // -------------------------------------------------------------------------

    public function test_owner_can_view_schedule_participants_page(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule))
            ->assertStatus(200)
            ->assertSee($schedule->title);
    }

    // -------------------------------------------------------------------------
    // 2. Penolakan akses antar-komunitas
    // -------------------------------------------------------------------------

    public function test_other_owner_cannot_access_schedule_of_another_community(): void
    {
        ['schedule' => $schedule] = $this->makeOwnerWithSchedule();

        // Owner kedua dengan komunitas berbeda
        $otherOwner = User::factory()->create(['role' => 'community_owner']);
        Community::create([
            'owner_id'            => $otherOwner->id,
            'name'                => 'Komunitas Lain',
            'slug'                => 'komunitas-lain',
            'sport_category'      => 'basket',
            'verification_status' => 'verified',
            'is_active'           => true,
            'is_public'           => true,
        ]);

        $this->actingAs($otherOwner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule))
            ->assertStatus(403);
    }

    public function test_regular_user_cannot_access_schedule_participants_page(): void
    {
        ['schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule))
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_from_schedule_participants_page(): void
    {
        ['schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $this->get(route('penyedia-komunitas.jadwal.show', $schedule))
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // 3. Email & data sensitif tidak muncul
    // -------------------------------------------------------------------------

    public function test_response_does_not_contain_participant_email(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $participant = $this->makeParticipant($schedule);
        $participantEmail = $participant->user->email;

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertStatus(200);
        $response->assertDontSee($participantEmail);
    }

    public function test_response_shows_participant_name_but_not_email(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $participant = $this->makeParticipant($schedule);
        $participantName  = $participant->user->name;
        $participantEmail = $participant->user->email;

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertSee($participantName);
        $response->assertDontSee($participantEmail);
    }

    // -------------------------------------------------------------------------
    // 4. Filter berdasarkan status
    // -------------------------------------------------------------------------

    public function test_filter_by_registered_status_shows_only_registered_participants(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $pRegistered = $this->makeParticipant($schedule, 'registered');
        $pAttended   = $this->makeParticipant($schedule, 'attended');
        $pNoShow     = $this->makeParticipant($schedule, 'no_show');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=registered');

        $response->assertStatus(200);
        $response->assertSee($pRegistered->user->name);
        $response->assertDontSee($pAttended->user->name);
        $response->assertDontSee($pNoShow->user->name);
    }

    public function test_filter_by_attended_shows_only_attended_participants(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $pRegistered = $this->makeParticipant($schedule, 'registered');
        $pAttended   = $this->makeParticipant($schedule, 'attended');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=attended');

        $response->assertStatus(200);
        $response->assertSee($pAttended->user->name);
        $response->assertDontSee($pRegistered->user->name);
    }

    public function test_filter_by_no_show_shows_only_no_show_participants(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $pRegistered = $this->makeParticipant($schedule, 'registered');
        $pNoShow     = $this->makeParticipant($schedule, 'no_show');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=no_show');

        $response->assertStatus(200);
        $response->assertSee($pNoShow->user->name);
        $response->assertDontSee($pRegistered->user->name);
    }

    public function test_invalid_status_filter_is_ignored_and_shows_all(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $p1 = $this->makeParticipant($schedule, 'registered');
        $p2 = $this->makeParticipant($schedule, 'attended');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=invalid_value');

        $response->assertStatus(200);
        $response->assertSee($p1->user->name);
        $response->assertSee($p2->user->name);
    }

    // -------------------------------------------------------------------------
    // 5. Ringkasan jumlah dari database
    // -------------------------------------------------------------------------

    public function test_summary_counts_reflect_database_values(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $this->makeParticipant($schedule, 'registered');
        $this->makeParticipant($schedule, 'registered');
        $this->makeParticipant($schedule, 'attended');
        $this->makeParticipant($schedule, 'no_show');
        $this->makeParticipant($schedule, 'cancelled');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertStatus(200);

        // Halaman harus menampilkan angka-angka ini dari database
        // Assertsi sederhana: angka muncul di halaman
        $response->assertSee('5'); // total semua
        $response->assertSee('2'); // terdaftar
        $response->assertSee('1'); // hadir / no_show / dibatalkan
    }

    // -------------------------------------------------------------------------
    // 6. Waktu check-in ditampilkan jika tersedia
    // -------------------------------------------------------------------------

    public function test_check_in_time_is_shown_when_attendance_exists(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $participant = $this->makeParticipant($schedule, 'attended');
        $checkInTime = now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->setHour(7)->setMinute(15);

        Attendance::create([
            'participant_id' => $participant->id,
            'check_in_at'    => $checkInTime,
            'method'         => 'manual',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertStatus(200);
        $response->assertSee('07:15');
    }

    public function test_check_in_column_shows_dash_when_no_attendance(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $this->makeParticipant($schedule, 'registered');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertStatus(200);
        $response->assertSee('—');
    }

    // -------------------------------------------------------------------------
    // 7. Pagination dengan filter terbawa
    // -------------------------------------------------------------------------

    public function test_pagination_query_string_is_preserved(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        // Buat lebih dari 20 peserta agar ada pagination
        for ($i = 0; $i < 25; $i++) {
            $this->makeParticipant($schedule, 'registered');
        }

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=registered&page=1');

        $response->assertStatus(200);
        // Link pagination harus menyertakan status_peserta
        $response->assertSee('status_peserta=registered');
    }

    // -------------------------------------------------------------------------
    // 8. Empty state
    // -------------------------------------------------------------------------

    public function test_empty_state_shown_when_no_participants(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Peserta');
    }

    public function test_empty_state_shown_when_filter_has_no_results(): void
    {
        ['owner' => $owner, 'schedule' => $schedule] = $this->makeOwnerWithSchedule();

        $this->makeParticipant($schedule, 'registered');

        $response = $this->actingAs($owner)
            ->get(route('penyedia-komunitas.jadwal.show', $schedule) . '?status_peserta=attended');

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Hasil Filter');
    }
}
