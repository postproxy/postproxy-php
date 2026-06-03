<?php

namespace PostProxy\Tests;

use PostProxy\Types\Message;
use PostProxy\Types\PaginatedResponse;

class MessagesTest extends TestCase
{
    private const MOCK_INBOUND = [
        'id' => 'msg_111',
        'chat_id' => 'chat_xyz789',
        'external_id' => 'mid.abc123',
        'direction' => 'inbound',
        'body' => 'Hey, do you ship internationally?',
        'status' => 'received',
        'tag' => null,
        'external_comment_id' => null,
        'error_message' => null,
        'platform_data' => null,
        'external_posted_at' => '2026-05-31T14:02:00.000Z',
        'external_delivered_at' => null,
        'external_read_at' => null,
        'external_edited_at' => null,
        'reactions' => [
            ['sender_external_id' => 'psid_123', 'emoji' => '❤️', 'reaction' => 'love', 'at' => '2026-05-31T14:04:00.000Z'],
        ],
        'attachments' => [
            ['id' => 'att_1', 'type' => 'image', 'url' => 'https://storage.postproxy.dev/a.jpg', 'status' => 'processed', 'external_id' => null],
        ],
        'is_unsupported' => false,
        'created_at' => '2026-05-31T14:02:01.000Z',
    ];

    private const MOCK_OUTBOUND = [
        'id' => 'msg_222',
        'chat_id' => 'chat_xyz789',
        'external_id' => null,
        'direction' => 'outbound',
        'body' => 'Yes, we ship worldwide!',
        'status' => 'pending',
        'tag' => null,
        'external_comment_id' => null,
        'error_message' => null,
        'platform_data' => null,
        'external_posted_at' => null,
        'attachments' => [],
        'reactions' => [],
        'is_unsupported' => false,
        'created_at' => '2026-05-31T15:30:05.000Z',
    ];

    public function testListMessages(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [self::MOCK_INBOUND],
            'total' => 1,
            'page' => 0,
            'per_page' => 20,
        ]);

        $result = $client->messages()->list('chat_xyz789', direction: 'inbound');

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $msg = $result->data[0];
        $this->assertInstanceOf(Message::class, $msg);
        $this->assertEquals('inbound', $msg->direction);
        $this->assertEquals('love', $msg->reactions[0]->reaction);
        $this->assertEquals('image', $msg->attachments[0]->type);
        $this->assertInstanceOf(\DateTimeImmutable::class, $msg->createdAt);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('/chats/chat_xyz789/messages', $uri);
        $this->assertStringContainsString('direction=inbound', $uri);
    }

    public function testSendTextMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, self::MOCK_OUTBOUND);

        $msg = $client->messages()->send('chat_xyz789', body: 'Yes, we ship worldwide!');

        $this->assertEquals('msg_222', $msg->id);
        $this->assertEquals('pending', $msg->status);

        $body = $this->lastRequestBody();
        $this->assertEquals('Yes, we ship worldwide!', $body['body']);
    }

    public function testSendMessageWithTag(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, self::MOCK_OUTBOUND);

        $client->messages()->send('chat_xyz789', body: 'Following up.', tag: 'HUMAN_AGENT');

        $body = $this->lastRequestBody();
        $this->assertEquals('HUMAN_AGENT', $body['tag']);
    }

    public function testSendMediaUrlMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, self::MOCK_OUTBOUND);

        $client->messages()->send('chat_xyz789', media: ['https://cdn.example.com/photo.png']);

        $body = $this->lastRequestBody();
        $this->assertEquals(['https://cdn.example.com/photo.png'], $body['media']);
    }

    public function testSendMediaFileUsesMultipart(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(202, self::MOCK_OUTBOUND);

        $tmpFile = tempnam(sys_get_temp_dir(), 'msg_') . '.png';
        file_put_contents($tmpFile, 'fake-png-data');

        try {
            $client->messages()->send('chat_xyz789', body: 'See attached', mediaFiles: [$tmpFile]);

            $request = $this->lastRequest();
            $this->assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));

            $body = (string) $request->getBody();
            $this->assertStringContainsString('media[]', $body);
            $this->assertStringContainsString('fake-png-data', $body);
            $this->assertStringContainsString('See attached', $body);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testGetMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::MOCK_INBOUND);

        $msg = $client->messages()->get('msg_111');

        $this->assertEquals('msg_111', $msg->id);
        $this->assertStringContainsString('/messages/msg_111', $this->lastRequestUri());
    }

    public function testEditMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, array_merge(self::MOCK_OUTBOUND, ['body' => 'Updated']));

        $msg = $client->messages()->edit('msg_222', body: 'Updated');

        $this->assertEquals('Updated', $msg->body);
        $this->assertEquals('PATCH', $this->lastRequest()->getMethod());

        $body = $this->lastRequestBody();
        $this->assertEquals('Updated', $body['body']);
    }

    public function testReactMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::MOCK_INBOUND);

        $msg = $client->messages()->react('msg_111', reaction: 'love', emoji: '❤️');

        $this->assertEquals('msg_111', $msg->id);
        $this->assertStringContainsString('/messages/msg_111/react', $this->lastRequestUri());

        $body = $this->lastRequestBody();
        $this->assertEquals('love', $body['reaction']);
    }

    public function testUnreactMessage(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::MOCK_INBOUND);

        $msg = $client->messages()->unreact('msg_111');

        $this->assertEquals('msg_111', $msg->id);
        $this->assertEquals('DELETE', $this->lastRequest()->getMethod());
        $this->assertStringContainsString('/messages/msg_111/unreact', $this->lastRequestUri());
    }
}
