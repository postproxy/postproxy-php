<?php

namespace PostProxy\Tests;

use PostProxy\Types\DeleteResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\PlatformParams\FacebookParams;
use PostProxy\Types\PlatformParams\PlatformParams;
use PostProxy\Types\Post;

class PostsTest extends TestCase
{
    public function testListReturnsPaginatedPosts(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [
                ['id' => 'post-1', 'body' => 'Hello', 'status' => 'processed', 'created_at' => '2025-01-01T00:00:00Z', 'platforms' => []],
            ],
            'total' => 1,
            'page' => 1,
            'per_page' => 10,
        ]);

        $result = $client->posts()->list();

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Post::class, $result->data[0]);
        $this->assertEquals('post-1', $result->data[0]->id);
        $this->assertEquals(1, $result->total);
        $this->assertEquals(1, $result->page);
        $this->assertEquals(10, $result->perPage);
    }

    public function testListSendsFilterParameters(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => [], 'total' => 0, 'page' => 2, 'per_page' => 5]);

        $client->posts()->list(page: 2, perPage: 5, status: 'draft');

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('page=2', $uri);
        $this->assertStringContainsString('per_page=5', $uri);
        $this->assertStringContainsString('status=draft', $uri);
    }

    public function testGetReturnsSinglePostWithPlatformResults(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'post-1',
            'body' => 'Hello',
            'status' => 'processed',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [
                [
                    'platform' => 'instagram',
                    'status' => 'published',
                    'attempted_at' => '2025-01-01T00:01:00Z',
                    'insights' => ['impressions' => 100, 'on' => '2025-01-02T00:00:00Z'],
                ],
            ],
        ]);

        $post = $client->posts()->get('post-1');

        $this->assertEquals('post-1', $post->id);
        $this->assertCount(1, $post->platforms);
        $this->assertEquals('instagram', $post->platforms[0]->platform);
        $this->assertEquals('published', $post->platforms[0]->status);
        $this->assertEquals(100, $post->platforms[0]->insights->impressions);
    }

    public function testCreateWithJsonPayload(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'post-new',
            'body' => 'Test post',
            'status' => 'pending',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [],
        ]);

        $post = $client->posts()->create('Test post', profiles: ['prof-1']);

        $this->assertEquals('post-new', $post->id);
        $this->assertEquals('Test post', $post->body);

        $body = $this->lastRequestBody();
        $this->assertEquals('Test post', $body['post']['body']);
        $this->assertEquals(['prof-1'], $body['profiles']);
    }

    public function testCreateIncludesMediaUrls(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'post-media',
            'body' => 'With media',
            'status' => 'pending',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [],
        ]);

        $client->posts()->create('With media', profiles: ['prof-1'], media: ['https://example.com/img.jpg']);

        $body = $this->lastRequestBody();
        $this->assertEquals(['https://example.com/img.jpg'], $body['media']);
    }

    public function testCreateIncludesPlatformParams(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'post-plat',
            'body' => 'Platform post',
            'status' => 'pending',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [],
        ]);

        $platforms = new PlatformParams([
            'facebook' => new FacebookParams(['format' => 'post', 'first_comment' => 'Hi!']),
        ]);

        $client->posts()->create('Platform post', profiles: ['prof-1'], platforms: $platforms);

        $body = $this->lastRequestBody();
        $this->assertEquals('post', $body['platforms']['facebook']['format']);
        $this->assertEquals('Hi!', $body['platforms']['facebook']['first_comment']);
    }

    public function testPublishDraft(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'id' => 'post-1',
            'body' => 'Draft',
            'status' => 'processing',
            'created_at' => '2025-01-01T00:00:00Z',
            'platforms' => [],
        ]);

        $post = $client->posts()->publishDraft('post-1');

        $this->assertEquals('processing', $post->status);
    }

    public function testDelete(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['deleted' => true]);

        $result = $client->posts()->delete('post-1');

        $this->assertInstanceOf(DeleteResponse::class, $result);
        $this->assertTrue($result->deleted);
    }
}
