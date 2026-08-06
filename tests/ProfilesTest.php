<?php

namespace PostProxy\Tests;

use PostProxy\Types\IceBreaker;
use PostProxy\Types\IceBreakersResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Exceptions\ConflictException;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\Placement;
use PostProxy\Types\PostSync;
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

    public function testAssignPlacementToGroup(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'pl-1',
            'name' => 'Feed',
            'metadata' => [],
            'profile_group_id' => 'pg-2',
        ]);

        $result = $client->profiles()->assignPlacementToGroup('prof-1', 'pl-1', 'pg-2');

        $this->assertInstanceOf(Placement::class, $result);
        $this->assertEquals('pg-2', $result->profileGroupId);
        $this->assertEquals('PATCH', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/profiles/prof-1/assign_placement_to_group', $this->lastRequestUri());
        $this->assertEquals(
            ['placement_id' => 'pl-1', 'target_profile_group_id' => 'pg-2'],
            $this->lastRequestBody(),
        );
    }

    public function testIceBreakersReturnsList(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'ice_breakers' => [
                ['question' => 'What do you do?', 'payload' => 'services'],
            ],
        ]);

        $result = $client->profiles()->iceBreakers('prof-1');

        $this->assertInstanceOf(IceBreakersResponse::class, $result);
        $this->assertCount(1, $result->iceBreakers);
        $this->assertInstanceOf(IceBreaker::class, $result->iceBreakers[0]);
        $this->assertEquals('What do you do?', $result->iceBreakers[0]->question);
        $this->assertStringContainsString('/profiles/prof-1/ice_breakers', $this->lastRequestUri());
    }

    public function testSetIceBreakers(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['success' => true]);

        $result = $client->profiles()->setIceBreakers('prof-1', [
            ['question' => 'What do you do?', 'payload' => 'services'],
        ]);

        $this->assertTrue($result->success);
        $this->assertEquals('POST', $this->lastRequest()->getMethod());
        $this->assertEquals(
            ['ice_breakers' => [['question' => 'What do you do?', 'payload' => 'services']]],
            $this->lastRequestBody(),
        );
    }

    public function testDeleteIceBreakers(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['success' => true]);

        $result = $client->profiles()->deleteIceBreakers('prof-1');

        $this->assertTrue($result->success);
        $this->assertEquals('DELETE', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/profiles/prof-1/ice_breakers', $this->lastRequestUri());
    }

    private const POST_SYNC = [
        'id' => 'sync456def',
        'profile_id' => 'prof-1',
        'kind' => 'posts',
        'trigger' => 'backfill',
        'status' => 'running',
        'started_at' => '2026-08-06T09:15:02.000Z',
        'completed_at' => null,
        'posts_seen' => 150,
        'posts_imported' => 143,
        'backfill_from' => '2025-01-01T00:00:00.000Z',
        'oldest_posted_at' => '2025-11-04T18:22:00.000Z',
        'error' => null,
        'created_at' => '2026-08-06T09:15:00.000Z',
    ];

    public function testBackfillPostsStartsARun(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, ['status' => 'pending'] + self::POST_SYNC);

        $sync = $client->profiles()->backfillPosts('prof-1', '2025-01-01');

        $this->assertInstanceOf(PostSync::class, $sync);
        $this->assertEquals('sync456def', $sync->id);
        $this->assertEquals('backfill', $sync->trigger);
        $this->assertEquals('pending', $sync->status);
        $this->assertEquals('POST', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/api/profiles/prof-1/backfill_posts', $this->lastRequestUri());
        $this->assertEquals(['from' => '2025-01-01'], $this->lastRequestBody());
    }

    public function testBackfillPostsSendsIdempotencyKey(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, self::POST_SYNC);

        $client->profiles()->backfillPosts('prof-1', '2025-01-01', idempotencyKey: 'key-1');

        $this->assertEquals('key-1', $this->lastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testBackfillPostsThrowsConflictWhenAlreadyRunning(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(409, [
            'error' => 'A posts backfill is already running for this profile',
            'profile_sync_id' => 'sync456def',
        ]);

        try {
            $client->profiles()->backfillPosts('prof-1', '2025-01-01');
            $this->fail('expected a ConflictException');
        } catch (ConflictException $e) {
            $this->assertEquals(409, $e->statusCode);
            $this->assertEquals('sync456def', $e->response['profile_sync_id']);
        }
    }

    public function testPostSyncsListsRunsWithFilters(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'total' => 1,
            'page' => 0,
            'per_page' => 25,
            'data' => [self::POST_SYNC],
        ]);

        $result = $client->profiles()->postSyncs('prof-1', trigger: 'backfill', status: 'running', perPage: 25);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertEquals(143, $result->data[0]->postsImported);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->data[0]->oldestPostedAt);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('/api/profiles/prof-1/post_syncs', $uri);
        $this->assertStringContainsString('trigger=backfill', $uri);
        $this->assertStringContainsString('status=running', $uri);
        $this->assertStringContainsString('per_page=25', $uri);
    }

    public function testPostSyncFetchesASingleRun(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['status' => 'completed'] + self::POST_SYNC);

        $sync = $client->profiles()->postSync('prof-1', 'sync456def');

        $this->assertEquals('completed', $sync->status);
        $this->assertStringContainsString('/api/profiles/prof-1/post_syncs/sync456def', $this->lastRequestUri());
    }
}
