<?php

namespace PostProxy\Tests;

use PostProxy\Exceptions\BadRequestException;
use PostProxy\Types\PlatformStats;
use PostProxy\Types\PostStats;
use PostProxy\Types\StatsRecord;
use PostProxy\Types\StatsResponse;

class StatsTest extends TestCase
{
    private function sampleStatsResponse(): array
    {
        return [
            'data' => [
                'abc123' => [
                    'platforms' => [
                        [
                            'profile_id' => 'prof_abc',
                            'platform' => 'instagram',
                            'records' => [
                                [
                                    'stats' => ['impressions' => 1200, 'likes' => 85, 'comments' => 12, 'saved' => 8],
                                    'recorded_at' => '2026-02-20T12:00:00Z',
                                ],
                                [
                                    'stats' => ['impressions' => 1523, 'likes' => 102, 'comments' => 15, 'saved' => 11],
                                    'recorded_at' => '2026-02-21T04:00:00Z',
                                ],
                            ],
                        ],
                    ],
                ],
                'def456' => [
                    'platforms' => [
                        [
                            'profile_id' => 'prof_def',
                            'platform' => 'twitter',
                            'records' => [
                                [
                                    'stats' => ['impressions' => 430, 'likes' => 22, 'retweets' => 5],
                                    'recorded_at' => '2026-02-20T12:00:00Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testStatsReturnsStatsResponse(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, $this->sampleStatsResponse());

        $result = $client->posts()->stats(['abc123', 'def456']);

        $this->assertInstanceOf(StatsResponse::class, $result);
        $this->assertCount(2, $result->data);
        $this->assertArrayHasKey('abc123', $result->data);
        $this->assertArrayHasKey('def456', $result->data);
    }

    public function testStatsPostHasPlatforms(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, $this->sampleStatsResponse());

        $result = $client->posts()->stats(['abc123', 'def456']);

        $postStats = $result->data['abc123'];
        $this->assertInstanceOf(PostStats::class, $postStats);
        $this->assertCount(1, $postStats->platforms);

        $platform = $postStats->platforms[0];
        $this->assertInstanceOf(PlatformStats::class, $platform);
        $this->assertEquals('prof_abc', $platform->profileId);
        $this->assertEquals('instagram', $platform->platform);
    }

    public function testStatsPlatformHasRecords(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, $this->sampleStatsResponse());

        $result = $client->posts()->stats(['abc123', 'def456']);
        $records = $result->data['abc123']->platforms[0]->records;

        $this->assertCount(2, $records);
        $this->assertInstanceOf(StatsRecord::class, $records[0]);
        $this->assertEquals(1200, $records[0]->stats['impressions']);
        $this->assertEquals(85, $records[0]->stats['likes']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $records[0]->recordedAt);
        $this->assertEquals('2026-02-20', $records[0]->recordedAt->format('Y-m-d'));
    }

    public function testStatsSecondPost(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, $this->sampleStatsResponse());

        $result = $client->posts()->stats(['abc123', 'def456']);
        $twitter = $result->data['def456']->platforms[0];

        $this->assertEquals('twitter', $twitter->platform);
        $this->assertEquals('prof_def', $twitter->profileId);
        $this->assertCount(1, $twitter->records);
        $this->assertEquals(430, $twitter->records[0]->stats['impressions']);
        $this->assertEquals(5, $twitter->records[0]->stats['retweets']);
    }

    public function testStatsSendsPostIds(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->posts()->stats(['abc123', 'def456']);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('post_ids=abc123%2Cdef456', $uri);
        $this->assertEquals('GET', $this->lastRequest()->getMethod());
    }

    public function testStatsSendsProfilesFilter(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->posts()->stats(['abc123'], profiles: ['instagram', 'twitter']);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('profiles=instagram%2Ctwitter', $uri);
    }

    public function testStatsSendsTimeRange(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->posts()->stats(
            ['abc123'],
            from: '2026-02-01T00:00:00Z',
            to: '2026-02-24T00:00:00Z',
        );

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('from=2026-02-01T00%3A00%3A00Z', $uri);
        $this->assertStringContainsString('to=2026-02-24T00%3A00%3A00Z', $uri);
    }

    public function testStatsSendsDateTimeInterfaceTimeRange(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->posts()->stats(
            ['abc123'],
            from: new \DateTimeImmutable('2026-02-01T00:00:00Z'),
            to: new \DateTimeImmutable('2026-02-24T00:00:00Z'),
        );

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('from=', $uri);
        $this->assertStringContainsString('to=', $uri);
    }

    public function testStatsHandlesBadRequest(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(400, [
            'status' => 400,
            'error' => 'Bad Request',
            'message' => 'param is missing or the value is empty: post_ids',
        ]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('param is missing or the value is empty: post_ids');

        $client->posts()->stats([]);
    }

    public function testStatsWithEmptyResults(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $result = $client->posts()->stats(['nonexistent']);

        $this->assertInstanceOf(StatsResponse::class, $result);
        $this->assertEmpty($result->data);
    }
}
