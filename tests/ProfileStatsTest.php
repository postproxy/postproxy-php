<?php

namespace PostProxy\Tests;

use PostProxy\Types\ProfileStatsResponse;

class ProfileStatsTest extends TestCase
{
    public function test_get_profile_stats_with_placement(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [
                'profile_id' => 'pf1',
                'platform' => 'linkedin',
                'placement_id' => 'org_1',
                'records' => [[
                    'stats' => ['followerCount' => 100],
                    'recorded_at' => '2026-05-12T00:00:00Z',
                ]],
            ],
        ]);

        $result = $client->profiles()->getProfileStats(
            'pf1', placementId: 'org_1', from: '2026-04-01T00:00:00Z'
        );

        $this->assertInstanceOf(ProfileStatsResponse::class, $result);
        $this->assertSame('pf1', $result->data->profileId);
        $this->assertSame(100, $result->data->records[0]->stats['followerCount']);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('/profiles/pf1/stats', $uri);
        $this->assertStringContainsString('placement_id=org_1', $uri);
    }

    public function test_get_profile_stats_omits_placement(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => ['profile_id' => 'bsky1', 'platform' => 'bluesky', 'placement_id' => null, 'records' => []],
        ]);

        $client->profiles()->getProfileStats('bsky1');
        $this->assertStringNotContainsString('placement_id', $this->lastRequestUri());
    }
}
