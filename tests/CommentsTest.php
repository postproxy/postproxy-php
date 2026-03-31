<?php

namespace PostProxy\Tests;

use PostProxy\Types\AcceptedResponse;
use PostProxy\Types\Comment;
use PostProxy\Types\PaginatedResponse;

class CommentsTest extends TestCase
{
    private const MOCK_COMMENT = [
        'id' => 'cmt_abc123',
        'external_id' => '17858893269123456',
        'body' => 'Great post!',
        'status' => 'synced',
        'author_username' => 'someuser',
        'author_avatar_url' => null,
        'author_external_id' => '12345',
        'parent_external_id' => null,
        'like_count' => 3,
        'is_hidden' => false,
        'permalink' => null,
        'platform_data' => null,
        'posted_at' => '2026-03-25T10:00:00Z',
        'created_at' => '2026-03-25T10:01:00Z',
        'replies' => [
            [
                'id' => 'cmt_def456',
                'external_id' => '17858893269123457',
                'body' => 'Thanks!',
                'status' => 'synced',
                'author_username' => 'author',
                'author_avatar_url' => null,
                'author_external_id' => '67890',
                'parent_external_id' => '17858893269123456',
                'like_count' => 1,
                'is_hidden' => false,
                'permalink' => null,
                'platform_data' => null,
                'posted_at' => '2026-03-25T10:05:00Z',
                'created_at' => '2026-03-25T10:05:00Z',
                'replies' => [],
            ],
        ],
    ];

    public function testListComments(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [self::MOCK_COMMENT],
            'total' => 1,
            'page' => 0,
            'per_page' => 20,
        ]);

        $result = $client->comments()->list('post1', 'prof1');

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Comment::class, $result->data[0]);
        $this->assertEquals('cmt_abc123', $result->data[0]->id);
        $this->assertEquals('Great post!', $result->data[0]->body);
        $this->assertCount(1, $result->data[0]->replies);
        $this->assertEquals('cmt_def456', $result->data[0]->replies[0]->id);

        $this->assertStringContainsString('profile_id=prof1', $this->lastRequestUri());
    }

    public function testListCommentsWithPagination(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [],
            'total' => 42,
            'page' => 2,
            'per_page' => 10,
        ]);

        $result = $client->comments()->list('post1', 'prof1', page: 2, perPage: 10);

        $this->assertEquals(42, $result->total);
        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('page=2', $uri);
        $this->assertStringContainsString('per_page=10', $uri);
    }

    public function testGetComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::MOCK_COMMENT);

        $comment = $client->comments()->get('post1', 'cmt_abc123', 'prof1');

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('cmt_abc123', $comment->id);
        $this->assertEquals('Great post!', $comment->body);
        $this->assertEquals(3, $comment->likeCount);
        $this->assertStringContainsString('/posts/post1/comments/cmt_abc123', $this->lastRequestUri());
    }

    public function testCreateComment(): void
    {
        $client = $this->mockClient();
        $created = array_merge(self::MOCK_COMMENT, [
            'id' => 'cmt_new',
            'body' => 'Nice!',
            'status' => 'pending',
            'external_id' => null,
            'replies' => [],
        ]);
        $this->queueResponse(200, $created);

        $comment = $client->comments()->create('post1', 'prof1', 'Nice!');

        $this->assertEquals('cmt_new', $comment->id);
        $this->assertEquals('pending', $comment->status);

        $body = $this->lastRequestBody();
        $this->assertEquals('Nice!', $body['text']);
        $this->assertArrayNotHasKey('parent_id', $body);
    }

    public function testCreateReply(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, array_merge(self::MOCK_COMMENT, [
            'id' => 'cmt_reply',
            'body' => 'Thanks!',
            'status' => 'pending',
            'replies' => [],
        ]));

        $comment = $client->comments()->create('post1', 'prof1', 'Thanks!', parentId: 'cmt_abc123');

        $body = $this->lastRequestBody();
        $this->assertEquals('Thanks!', $body['text']);
        $this->assertEquals('cmt_abc123', $body['parent_id']);
    }

    public function testDeleteComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['accepted' => true]);

        $result = $client->comments()->delete('post1', 'cmt_abc123', 'prof1');

        $this->assertInstanceOf(AcceptedResponse::class, $result);
        $this->assertTrue($result->accepted);
        $this->assertEquals('DELETE', $this->lastRequest()->getMethod());
    }

    public function testHideComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['accepted' => true]);

        $result = $client->comments()->hide('post1', 'cmt_abc123', 'prof1');

        $this->assertTrue($result->accepted);
        $this->assertStringContainsString('/comments/cmt_abc123/hide', $this->lastRequestUri());
    }

    public function testUnhideComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['accepted' => true]);

        $result = $client->comments()->unhide('post1', 'cmt_abc123', 'prof1');

        $this->assertTrue($result->accepted);
        $this->assertStringContainsString('/comments/cmt_abc123/unhide', $this->lastRequestUri());
    }

    public function testLikeComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['accepted' => true]);

        $result = $client->comments()->like('post1', 'cmt_abc123', 'prof1');

        $this->assertTrue($result->accepted);
        $this->assertStringContainsString('/comments/cmt_abc123/like', $this->lastRequestUri());
    }

    public function testUnlikeComment(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['accepted' => true]);

        $result = $client->comments()->unlike('post1', 'cmt_abc123', 'prof1');

        $this->assertTrue($result->accepted);
        $this->assertStringContainsString('/comments/cmt_abc123/unlike', $this->lastRequestUri());
    }
}
