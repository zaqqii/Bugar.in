<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityBlock;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected User $ownerA;
    protected User $ownerB;
    protected Community $communityA;
    protected Community $communityB;
    protected Schedule $scheduleA;
    protected Schedule $scheduleB;
    protected User $participantUser;
    protected Participant $participantA;
    protected Participant $participantB;

    protected function setUp(): void
    {
        parent::setUp();

        $tz = config('app.timezone', 'Asia/Jakarta');
        Carbon::setTestNow(Carbon::parse('2026-11-14 10:00:00', $tz));

        // Create Community Owner A & Community A
        $this->ownerA = User::factory()->create([
            'role' => 'community_owner',
            'email' => 'owner.a@bugar.in',
        ]);
        $this->communityA = Community::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Komunitas Lari A',
            'slug' => 'komunitas-lari-a',
            'sport_category' => 'running',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        // Create Schedule A
        $this->scheduleA = Schedule::create([
            'community_id' => $this->communityA->id,
            'date' => '2026-11-15',
            'title' => 'Sesi Lari Pagi A',
            'day_of_week' => 'sunday',
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'status' => 'open',
            'is_active' => true,
        ]);

        // Create Community Owner B & Community B
        $this->ownerB = User::factory()->create([
            'role' => 'community_owner',
            'email' => 'owner.b@bugar.in',
        ]);
        $this->communityB = Community::create([
            'owner_id' => $this->ownerB->id,
            'name' => 'Komunitas Lari B',
            'slug' => 'komunitas-lari-b',
            'sport_category' => 'running',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        // Create Schedule B
        $this->scheduleB = Schedule::create([
            'community_id' => $this->communityB->id,
            'date' => '2026-11-15',
            'title' => 'Sesi Lari Pagi B',
            'day_of_week' => 'sunday',
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'status' => 'open',
            'is_active' => true,
        ]);

        // Create a Participant User with sensitive health and private fields
        $this->participantUser = User::factory()->create([
            'name' => 'Budi Raharjo',
            'username' => 'budiraharjo',
            'email' => 'budi.raharjo.private@example.com',
            'phone' => '081298765432',
            'height_cm' => 175.50,
            'weight_kg' => 72.30,
            'role' => 'user',
        ]);

        // Register participant to Schedule A
        $this->participantA = Participant::create([
            'user_id' => $this->participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $this->scheduleA->id,
            'status' => 'registered',
            'registered_at' => Carbon::now($tz),
        ]);

        // Register participant to Schedule B
        $this->participantB = Participant::create([
            'user_id' => $this->participantUser->id,
            'participatable_type' => Schedule::class,
            'participatable_id' => $this->scheduleB->id,
            'status' => 'registered',
            'registered_at' => Carbon::now($tz),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_schedule_show_does_not_reveal_participant_email_or_sensitive_health_data(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('penyedia-komunitas.jadwal.show', $this->scheduleA));

        $response->assertStatus(200);
        $response->assertSee('Budi Raharjo');
        $response->assertSee('@budiraharjo');
        $response->assertDontSee('budi.raharjo.private@example.com');
        $response->assertDontSee('081298765432');
        $response->assertDontSee('175.50');
        $response->assertDontSee('72.30');
    }

    public function test_checkin_view_does_not_contain_participant_email_in_embedded_json_or_html(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('penyedia-komunitas.jadwal.checkin', $this->scheduleA));

        $response->assertStatus(200);
        $response->assertSee('Budi Raharjo');
        $response->assertDontSee('budi.raharjo.private@example.com');
        $response->assertDontSee('081298765432');
        $response->assertDontSee('175.50');
        $response->assertDontSee('72.30');
    }

    public function test_checkin_participants_json_endpoint_excludes_email_and_sensitive_fields(): void
    {
        $response = $this->actingAs($this->ownerA)->getJson(route('penyedia-komunitas.jadwal.checkin.participants', $this->scheduleA));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => ['total', 'attended', 'registered'],
            'participants' => [
                '*' => [
                    'id',
                    'user_id',
                    'user_name',
                    'user_username',
                    'user_avatar',
                    'status',
                    'status_label',
                    'status_color',
                    'check_in_time',
                    'method',
                ]
            ]
        ]);

        $json = $response->json();
        $firstParticipant = $json['participants'][0];

        $this->assertEquals('Budi Raharjo', $firstParticipant['user_name']);
        $this->assertEquals('budiraharjo', $firstParticipant['user_username']);
        $this->assertArrayNotHasKey('user_email', $firstParticipant, 'user_email harus dihilangkan dari JSON response');
        $this->assertArrayNotHasKey('email', $firstParticipant, 'email harus dihilangkan dari JSON response');
        $this->assertArrayNotHasKey('phone', $firstParticipant);
        $this->assertArrayNotHasKey('height_cm', $firstParticipant);
        $this->assertArrayNotHasKey('weight_kg', $firstParticipant);
        $this->assertArrayNotHasKey('bmi', $firstParticipant);

        // Verify email is nowhere in the entire raw JSON text
        $rawContent = $response->getContent();
        $this->assertStringNotContainsString('budi.raharjo.private@example.com', $rawContent);
        $this->assertStringNotContainsString('081298765432', $rawContent);
    }

    public function test_blocked_users_page_does_not_reveal_email(): void
    {
        CommunityBlock::create([
            'community_id' => $this->communityA->id,
            'user_id' => $this->participantUser->id,
            'blocked_by' => $this->ownerA->id,
            'reason' => 'Pelanggaran tata tertib latihan',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('penyedia-komunitas.jadwal.blocks'));

        $response->assertStatus(200);
        $response->assertSee('Budi Raharjo');
        $response->assertSee('@budiraharjo');
        $response->assertDontSee('budi.raharjo.private@example.com');
    }

    public function test_cross_community_access_is_forbidden_for_schedule_participant_data(): void
    {
        // Owner B tries to access Schedule A's detail
        $response = $this->actingAs($this->ownerB)->get(route('penyedia-komunitas.jadwal.show', $this->scheduleA));
        $response->assertStatus(403);

        // Owner B tries to access Schedule A's checkin view
        $response = $this->actingAs($this->ownerB)->get(route('penyedia-komunitas.jadwal.checkin', $this->scheduleA));
        $response->assertStatus(403);

        // Owner B tries to access Schedule A's checkin participants JSON
        $response = $this->actingAs($this->ownerB)->getJson(route('penyedia-komunitas.jadwal.checkin.participants', $this->scheduleA));
        $response->assertStatus(403);

        // Owner B tries to remove participant from Schedule A
        $response = $this->actingAs($this->ownerB)->delete(route('penyedia-komunitas.jadwal.participants.remove', [$this->scheduleA, $this->participantA]));
        $response->assertStatus(403);

        // Owner B tries to block participant via Schedule A
        $response = $this->actingAs($this->ownerB)->post(route('penyedia-komunitas.jadwal.participants.block', [$this->scheduleA, $this->participantA]), [
            'reason' => 'Tes blokir ilegal',
        ]);
        $response->assertStatus(403);
    }

    public function test_accessing_participant_from_different_schedule_is_rejected(): void
    {
        // Owner A tries to remove Participant B (who belongs to Schedule B) from Schedule A
        $response = $this->actingAs($this->ownerA)->delete(route('penyedia-komunitas.jadwal.participants.remove', [$this->scheduleA, $this->participantB]));
        $response->assertStatus(404);

        // Owner A tries to block Participant B (belonging to Schedule B) via Schedule A
        $response = $this->actingAs($this->ownerA)->post(route('penyedia-komunitas.jadwal.participants.block', [$this->scheduleA, $this->participantB]), [
            'reason' => 'Tes salah sesi',
        ]);
        $response->assertStatus(404);
    }
}
