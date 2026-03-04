<?php

namespace PostProxy\Tests;

use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\Webhook;
use PostProxy\Types\WebhookDelivery;
use PostProxy\WebhookSignature;

class WebhooksTest extends TestCase
{
    private const WEBHOOK_DATA = [
        'id' => 'wh-1',
        'url' => 'https://example.com/webhook',
        'events' => ['post.published', 'post.failed'],
        'enabled' => true,
        'description' => 'Test webhook',
        'secret' => 'whsec_test123',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-01T00:00:00Z',
    ];

    private const DELIVERY_DATA = [
        'id' => 'del-1',
        'event_id' => 'evt-1',
        'event_type' => 'post.published',
        'response_status' => 200,
        'attempt_number' => 1,
        'success' => true,
        'attempted_at' => '2025-01-01T00:00:00Z',
        'created_at' => '2025-01-01T00:00:00Z',
    ];

    public function testListWebhooks(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => [self::WEBHOOK_DATA]]);

        $result = $client->webhooks()->list();

        $this->assertInstanceOf(ListResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(Webhook::class, $result->data[0]);
        $this->assertEquals('wh-1', $result->data[0]->id);
    }

    public function testGetWebhook(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::WEBHOOK_DATA);

        $webhook = $client->webhooks()->get('wh-1');

        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertEquals('wh-1', $webhook->id);
        $this->assertEquals(['post.published', 'post.failed'], $webhook->events);
        $this->assertTrue($webhook->enabled);
    }

    public function testCreateWebhook(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, self::WEBHOOK_DATA);

        $webhook = $client->webhooks()->create(
            'https://example.com/webhook',
            ['post.published', 'post.failed'],
            description: 'Test webhook',
        );

        $this->assertEquals('wh-1', $webhook->id);

        $body = $this->lastRequestBody();
        $this->assertEquals('https://example.com/webhook', $body['url']);
        $this->assertEquals(['post.published', 'post.failed'], $body['events']);
        $this->assertEquals('Test webhook', $body['description']);
    }

    public function testUpdateWebhook(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, array_merge(self::WEBHOOK_DATA, ['enabled' => false]));

        $webhook = $client->webhooks()->update('wh-1', enabled: false);

        $this->assertFalse($webhook->enabled);

        $body = $this->lastRequestBody();
        $this->assertFalse($body['enabled']);
        $this->assertEquals('PATCH', $this->lastRequest()->getMethod());
    }

    public function testDeleteWebhook(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['deleted' => true]);

        $result = $client->webhooks()->delete('wh-1');

        $this->assertInstanceOf(DeleteResponse::class, $result);
        $this->assertTrue($result->deleted);
    }

    public function testWebhookDeliveries(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [self::DELIVERY_DATA],
            'total' => 1,
            'page' => 1,
            'per_page' => 10,
        ]);

        $result = $client->webhooks()->deliveries('wh-1', page: 1, perPage: 10);

        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(WebhookDelivery::class, $result->data[0]);
        $this->assertEquals('del-1', $result->data[0]->id);
        $this->assertTrue($result->data[0]->success);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('page=1', $uri);
        $this->assertStringContainsString('per_page=10', $uri);
    }

    public function testVerifySignatureValid(): void
    {
        $payload = '{"event":"post.published","data":{"id":"post-1"}}';
        $secret = 'whsec_test123';
        $signature = 't=1234567890,v1=c8e99efbb07ac8e3152c02dd8d83e8ddb803ae8fb001d9e1ab42fb0b1f405ef2';

        $this->assertTrue(WebhookSignature::verify($payload, $signature, $secret));
    }

    public function testVerifySignatureInvalid(): void
    {
        $payload = '{"event":"post.published","data":{"id":"post-1"}}';
        $secret = 'whsec_test123';
        $signature = 't=1234567890,v1=invalidsignature';

        $this->assertFalse(WebhookSignature::verify($payload, $signature, $secret));
    }

    public function testVerifySignatureWrongSecret(): void
    {
        $payload = '{"event":"post.published","data":{"id":"post-1"}}';
        $secret = 'wrong_secret';
        $signature = 't=1234567890,v1=c8e99efbb07ac8e3152c02dd8d83e8ddb803ae8fb001d9e1ab42fb0b1f405ef2';

        $this->assertFalse(WebhookSignature::verify($payload, $signature, $secret));
    }
}
