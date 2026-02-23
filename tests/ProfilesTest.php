<?php

namespace PostProxy\Tests;

use PostProxy\Types\ListResponse;
use PostProxy\Types\Profile;
use PostProxy\Types\SuccessResponse;

class ProfilesTest extends TestCase
{
    public function testListReturnsProfiles(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [
                ['id' => 'prof-1', 'name' => 'Test Profile', 'status' => 'active', 'platform' => 'instagram', 'profile_group_id' => 'pg-1', 'post_count' => 5],
            ],
        ]);

        $result = $client->profiles()->list();

        $this->assertInstanceOf(ListResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Profile::class, $result->data[0]);
        $this->assertEquals('prof-1', $result->data[0]->id);
        $this->assertEquals('Test Profile', $result->data[0]->name);
        $this->assertEquals('instagram', $result->data[0]->platform);
    }

    public function testListSendsProfileGroupId(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->profiles()->list(profileGroupId: 'pg-456');

        $this->assertStringContainsString('profile_group_id=pg-456', $this->lastRequestUri());
    }

    public function testGetReturnsSingleProfile(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'prof-1',
            'name' => 'My Profile',
            'status' => 'active',
            'platform' => 'facebook',
            'profile_group_id' => 'pg-1',
            'expires_at' => '2025-12-31T00:00:00Z',
            'post_count' => 10,
        ]);

        $profile = $client->profiles()->get('prof-1');

        $this->assertEquals('prof-1', $profile->id);
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->expiresAt);
        $this->assertEquals(10, $profile->postCount);
    }

    public function testPlacementsReturnsPlacementList(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [
                ['id' => 'feed', 'name' => 'Feed'],
                ['id' => 'story', 'name' => 'Story'],
            ],
        ]);

        $result = $client->profiles()->placements('prof-1');

        $this->assertCount(2, $result->data);
        $this->assertEquals('feed', $result->data[0]->id);
        $this->assertEquals('Story', $result->data[1]->name);
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['success' => true]);

        $result = $client->profiles()->delete('prof-1');

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertTrue($result->success);
    }
}
