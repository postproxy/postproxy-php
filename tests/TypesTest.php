<?php

namespace PostProxy\Tests;

use PostProxy\Types\Insights;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\PlatformParams\FacebookParams;
use PostProxy\Types\PlatformParams\InstagramParams;
use PostProxy\Types\PlatformParams\PlatformParams;
use PostProxy\Types\PlatformResult;
use PostProxy\Types\Post;
use PostProxy\Types\Profile;
use PostProxy\Types\ProfileGroup;

class TypesTest extends \PHPUnit\Framework\TestCase
{
    public function testPostParsesBasicAttributes(): void
    {
        $post = new Post([
            'id' => 'post-1',
            'body' => 'Hello',
            'status' => 'processed',
            'created_at' => '2025-01-01T00:00:00Z',
        ]);

        $this->assertEquals('post-1', $post->id);
        $this->assertEquals('Hello', $post->body);
        $this->assertEquals('processed', $post->status);
        $this->assertInstanceOf(\DateTimeImmutable::class, $post->createdAt);
        $this->assertEquals([], $post->platforms);
    }

    public function testPostParsesPlatformResultsWithInsights(): void
    {
        $post = new Post([
            'id' => 'post-1',
            'body' => 'Hello',
            'status' => 'processed',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [
                [
                    'platform' => 'instagram',
                    'status' => 'published',
                    'attempted_at' => '2025-01-01T00:01:00Z',
                    'insights' => ['impressions' => 250, 'on' => '2025-01-02T00:00:00Z'],
                ],
            ],
        ]);

        $this->assertCount(1, $post->platforms);
        $pr = $post->platforms[0];
        $this->assertInstanceOf(PlatformResult::class, $pr);
        $this->assertEquals('instagram', $pr->platform);
        $this->assertInstanceOf(Insights::class, $pr->insights);
        $this->assertEquals(250, $pr->insights->impressions);
    }

    public function testProfileParsesAttributes(): void
    {
        $profile = new Profile([
            'id' => 'prof-1',
            'name' => 'Test',
            'status' => 'active',
            'platform' => 'facebook',
            'profile_group_id' => 'pg-1',
            'post_count' => 42,
        ]);

        $this->assertEquals('prof-1', $profile->id);
        $this->assertEquals(42, $profile->postCount);
        $this->assertNull($profile->expiresAt);
    }

    public function testProfileGroupParsesAttributes(): void
    {
        $group = new ProfileGroup(['id' => 'pg-1', 'name' => 'Group', 'profiles_count' => 3]);

        $this->assertEquals('pg-1', $group->id);
        $this->assertEquals('Group', $group->name);
        $this->assertEquals(3, $group->profilesCount);
    }

    public function testPaginatedResponseWrapsData(): void
    {
        $response = new PaginatedResponse(
            data: [1, 2, 3],
            total: 100,
            page: 1,
            perPage: 3,
        );

        $this->assertEquals([1, 2, 3], $response->data);
        $this->assertEquals(100, $response->total);
        $this->assertEquals(1, $response->page);
        $this->assertEquals(3, $response->perPage);
    }

    public function testPlatformParamsSerializesExcludingNilValues(): void
    {
        $params = new PlatformParams([
            'facebook' => new FacebookParams(['format' => 'post', 'first_comment' => 'Hello']),
            'instagram' => new InstagramParams(['format' => 'reel']),
        ]);

        $h = $params->toArray();
        $this->assertEquals(['format' => 'post', 'first_comment' => 'Hello'], $h['facebook']);
        $this->assertEquals(['format' => 'reel'], $h['instagram']);
        $this->assertArrayNotHasKey('tiktok', $h);
        $this->assertArrayNotHasKey('twitter', $h);
    }

    public function testInstagramUserTagsSerializeAsPlainArrays(): void
    {
        $params = new \PostProxy\Types\PlatformParams\PlatformParams([
            'instagram' => new \PostProxy\Types\PlatformParams\InstagramParams([
                'format' => 'post',
                'user_tags' => [
                    new \PostProxy\Types\PlatformParams\InstagramUserTag('natgeo', 0.5, 0.4),
                    new \PostProxy\Types\PlatformParams\InstagramUserTag('nasa', 0.2, 0.8, 1),
                    // Video slides are tagged by username only.
                    new \PostProxy\Types\PlatformParams\InstagramUserTag('spacex', mediaIndex: 2),
                ],
            ]),
        ]);

        $tags = $params->toArray()['instagram']['user_tags'];

        $this->assertCount(3, $tags);
        $this->assertEquals(['username' => 'natgeo', 'x' => 0.5, 'y' => 0.4], $tags[0]);
        $this->assertEquals(['username' => 'spacex', 'media_index' => 2], $tags[2]);
    }

    public function testStatsRecordCarriesRawStats(): void
    {
        $record = new \PostProxy\Types\StatsRecord([
            'stats' => ['impressions' => 1200],
            'raw_stats' => ['views' => 1200, 'impression_count' => 1200],
            'recorded_at' => '2026-02-20T12:00:00Z',
        ]);

        $this->assertEquals(1200, $record->rawStats['views']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $record->recordedAt);
    }

    public function testStatsRecordDefaultsRawStatsToEmpty(): void
    {
        $record = new \PostProxy\Types\StatsRecord([
            'stats' => [],
            'recorded_at' => '2026-02-20T12:00:00Z',
        ]);

        $this->assertEquals([], $record->rawStats);
    }

    public function testPostSyncParsesTimestamps(): void
    {
        $sync = new \PostProxy\Types\PostSync([
            'id' => 'sync456def',
            'profile_id' => 'prof123abc',
            'kind' => 'posts',
            'trigger' => 'backfill',
            'status' => 'running',
            'started_at' => '2026-08-06T09:15:02.000Z',
            'completed_at' => null,
            'posts_seen' => 150,
            'posts_imported' => 143,
            'oldest_posted_at' => '2025-11-04T18:22:00.000Z',
            'created_at' => '2026-08-06T09:15:00.000Z',
        ]);

        $this->assertEquals('backfill', $sync->trigger);
        $this->assertLessThan($sync->postsSeen, $sync->postsImported);
        $this->assertInstanceOf(\DateTimeImmutable::class, $sync->startedAt);
        $this->assertNull($sync->completedAt);
    }
}
