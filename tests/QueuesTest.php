<?php

namespace PostProxy\Tests;

use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\NextSlotResponse;
use PostProxy\Types\Queue;

class QueuesTest extends TestCase
{
    private const MOCK_QUEUE = [
        'id' => 'q1abc',
        'name' => 'Morning Posts',
        'description' => 'Daily morning content',
        'timezone' => 'America/New_York',
        'enabled' => true,
        'jitter' => 10,
        'profile_group_id' => 'pg123',
        'timeslots' => [
            ['id' => 1, 'day' => 1, 'time' => '09:00'],
            ['id' => 2, 'day' => 3, 'time' => '09:00'],
            ['id' => 3, 'day' => 5, 'time' => '14:00'],
        ],
        'posts_count' => 5,
    ];

    public function testListQueues(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: ['data' => [self::MOCK_QUEUE]]);

        $result = $client->queues()->list();
        $this->assertInstanceOf(ListResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Queue::class, $result->data[0]);
        $this->assertEquals('q1abc', $result->data[0]->id);
        $this->assertCount(3, $result->data[0]->timeslots);
        $this->assertStringContainsString('/post_queues', $this->lastRequestUri());
    }

    public function testGetQueue(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: self::MOCK_QUEUE);

        $queue = $client->queues()->get('q1abc');
        $this->assertInstanceOf(Queue::class, $queue);
        $this->assertEquals('q1abc', $queue->id);
        $this->assertEquals('Morning Posts', $queue->name);
        $this->assertTrue($queue->enabled);
        $this->assertEquals(10, $queue->jitter);
        $this->assertStringContainsString('/post_queues/q1abc', $this->lastRequestUri());
    }

    public function testNextSlot(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: ['next_slot' => '2026-03-11T14:00:00Z']);

        $result = $client->queues()->nextSlot('q1abc');
        $this->assertInstanceOf(NextSlotResponse::class, $result);
        $this->assertEquals('2026-03-11T14:00:00Z', $result->nextSlot);
        $this->assertStringContainsString('/post_queues/q1abc/next_slot', $this->lastRequestUri());
    }

    public function testCreateQueue(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: self::MOCK_QUEUE);

        $queue = $client->queues()->create(
            'Morning Posts',
            'pg123',
            description: 'Daily morning content',
            timezone: 'America/New_York',
            jitter: 10,
            timeslots: [
                ['day' => 1, 'time' => '09:00'],
                ['day' => 3, 'time' => '09:00'],
            ],
        );
        $this->assertEquals('q1abc', $queue->id);

        $body = $this->lastRequestBody();
        $this->assertEquals('pg123', $body['profile_group_id']);
        $this->assertEquals('Morning Posts', $body['post_queue']['name']);
        $this->assertEquals('America/New_York', $body['post_queue']['timezone']);
        $this->assertEquals(10, $body['post_queue']['jitter']);
        $this->assertEquals([
            ['day' => 1, 'time' => '09:00'],
            ['day' => 3, 'time' => '09:00'],
        ], $body['post_queue']['queue_timeslots_attributes']);
    }

    public function testUpdateQueue(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: array_merge(self::MOCK_QUEUE, ['enabled' => false]));

        $queue = $client->queues()->update('q1abc', enabled: false);
        $this->assertFalse($queue->enabled);

        $body = $this->lastRequestBody();
        $this->assertFalse($body['post_queue']['enabled']);
        $this->assertEquals('PATCH', $this->lastRequest()->getMethod());
    }

    public function testUpdateQueueWithTimeslots(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: self::MOCK_QUEUE);

        $client->queues()->update('q1abc', timeslots: [
            ['day' => 2, 'time' => '10:00'],
            ['id' => 1, '_destroy' => true],
        ]);

        $body = $this->lastRequestBody();
        $this->assertEquals([
            ['day' => 2, 'time' => '10:00'],
            ['id' => 1, '_destroy' => true],
        ], $body['post_queue']['queue_timeslots_attributes']);
    }

    public function testDeleteQueue(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(body: ['deleted' => true]);

        $result = $client->queues()->delete('q1abc');
        $this->assertInstanceOf(DeleteResponse::class, $result);
        $this->assertTrue($result->deleted);
        $this->assertEquals('DELETE', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/post_queues/q1abc', $this->lastRequestUri());
    }
}
