<?php

namespace Tests\Unit;

use App\Models\Community;
use PHPUnit\Framework\TestCase;

class CommunityVerificationTest extends TestCase
{
    public function test_unverified_community_methods(): void
    {
        $community = new Community([
            'verification_status' => 'pending',
            'followers_count' => 5,
        ]);

        $this->assertFalse($community->isVerified());
        $this->assertFalse($community->canCreatePaidEvents());
        $this->assertFalse($community->canClaimFreeVerification());
        $this->assertEquals(10, $community->followersNeededForVerification());
    }

    public function test_community_with_15_followers_can_claim_free_verification(): void
    {
        $community = new Community([
            'verification_status' => 'pending',
            'followers_count' => 15,
        ]);

        $this->assertFalse($community->isVerified());
        $this->assertTrue($community->canClaimFreeVerification());
        $this->assertEquals(0, $community->followersNeededForVerification());
    }

    public function test_verified_community_can_create_paid_events(): void
    {
        $community = new Community([
            'verification_status' => 'verified',
            'followers_count' => 20,
        ]);

        $this->assertTrue($community->isVerified());
        $this->assertTrue($community->canCreatePaidEvents());
    }
}
