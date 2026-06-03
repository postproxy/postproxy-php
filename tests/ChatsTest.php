<?php

namespace PostProxy\Tests;

use PostProxy\Types\Chat;
use PostProxy\Types\PaginatedResponse;

class ChatsTest extends TestCase
{
    private const MOCK_CHAT = [
        'id' => 'chat_xyz789',
        'profile_id' => 'prof_abc123',
        'platform' => 'instagram',
        'participant_external_id' => 'igsid_8675309',
        'participant_username' => 'jane_doe',
        'participant_name' => 'Jane Doe',
        'participant_avatar_url' => 'https://storage.postproxy.dev/x.jpg',
        'external_conversation_id' => null,
        'last_inbound_at' => '2026-05-31T14:02:00.000Z',
        'last_outbound_at' => '2026-05-31T15:10:00.000Z',
        'last_message_at' => '2026-05-31T15:10:00.000Z',
        'metadata' => ['is_verified_user' => false, 'follower_count' => 482],
        'created_at' => '2026-04-12T08:00:00.000Z',
    ];

    public function testListChats(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [self::MOCK_CHAT],
            'total' => 1,
            'page' => 0,
            'per_page' => 20,
        ]);

        $result = $client->chats()->list('prof_abc123', perPage: 20);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Chat::class, $result->data[0]);
        $this->assertEquals('chat_xyz789', $result->data[0]->id);
        $this->assertEquals('jane_doe', $result->data[0]->participantUsername);
        $this->assertEquals(482, $result->data[0]->metadata['follower_count']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->data[0]->createdAt);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('/profiles/prof_abc123/chats', $uri);
        $this->assertStringContainsString('per_page=20', $uri);
    }

    public function testCreateChat(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(201, self::MOCK_CHAT);

        $chat = $client->chats()->create('prof_abc123', 'igsid_8675309', participantUsername: 'jane_doe');

        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertEquals('chat_xyz789', $chat->id);

        $body = $this->lastRequestBody();
        $this->assertEquals('igsid_8675309', $body['participant_external_id']);
        $this->assertEquals('jane_doe', $body['participant_username']);
        $this->assertStringContainsString('/profiles/prof_abc123/chats', $this->lastRequestUri());
    }

    public function testGetChat(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::MOCK_CHAT);

        $chat = $client->chats()->get('chat_xyz789');

        $this->assertEquals('chat_xyz789', $chat->id);
        $this->assertStringContainsString('/chats/chat_xyz789', $this->lastRequestUri());
    }

    public function testArchiveChat(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, array_merge(self::MOCK_CHAT, ['archived' => true]));

        $chat = $client->chats()->archive('chat_xyz789');

        $this->assertTrue($chat->archived);
        $this->assertEquals('POST', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/chats/chat_xyz789/archive', $this->lastRequestUri());
    }

    public function testUnarchiveChat(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, array_merge(self::MOCK_CHAT, ['archived' => false]));

        $chat = $client->chats()->unarchive('chat_xyz789');

        $this->assertFalse($chat->archived);
        $this->assertEquals('DELETE', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/chats/chat_xyz789/archive', $this->lastRequestUri());
    }
}
