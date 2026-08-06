<?php

namespace PostProxy\Tests;

use PostProxy\Exceptions\AuthenticationException;
use PostProxy\Exceptions\ConflictException;
use PostProxy\Exceptions\BadRequestException;
use PostProxy\Exceptions\NotFoundException;
use PostProxy\Exceptions\PostProxyException;
use PostProxy\Exceptions\ValidationException;

class ClientTest extends TestCase
{
    public function testSendsBearerToken(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->profiles()->list();

        $this->assertStringContainsString(
            'Bearer test-key',
            $this->lastRequest()->getHeaderLine('Authorization'),
        );
    }

    public function testSendsDefaultProfileGroupId(): void
    {
        $client = $this->mockClient(profileGroupId: 'pg-123');
        $this->queueResponse(200, ['data' => []]);

        $client->profiles()->list();

        $this->assertStringContainsString('profile_group_id=pg-123', $this->lastRequestUri());
    }

    public function testOverridesProfileGroupIdPerRequest(): void
    {
        $client = $this->mockClient(profileGroupId: 'pg-123');
        $this->queueResponse(200, ['data' => []]);

        $client->profiles()->list(profileGroupId: 'pg-override');

        $this->assertStringContainsString('profile_group_id=pg-override', $this->lastRequestUri());
    }

    public function testRaisesAuthenticationExceptionOn401(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(401, ['error' => 'Unauthorized']);

        $this->expectException(AuthenticationException::class);
        $client->profiles()->list();
    }

    public function testRaisesNotFoundExceptionOn404(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(404, ['error' => 'Not found']);

        $this->expectException(NotFoundException::class);
        $client->profiles()->get('bad-id');
    }

    public function testRaisesValidationExceptionOn422(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(422, ['error' => 'Invalid']);

        $this->expectException(ValidationException::class);
        $client->profiles()->list();
    }

    public function testRaisesBadRequestExceptionOn400(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(400, ['error' => 'Bad request']);

        $this->expectException(BadRequestException::class);
        $client->profiles()->list();
    }

    public function testRaisesPostProxyExceptionOnOtherStatus(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(500, ['error' => 'Server error']);

        $this->expectException(PostProxyException::class);
        $client->profiles()->list();
    }

    public function testExceptionContainsStatusCodeAndResponse(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(401, ['error' => 'Unauthorized']);

        try {
            $client->profiles()->list();
            $this->fail('Expected AuthenticationException');
        } catch (AuthenticationException $e) {
            $this->assertEquals(401, $e->statusCode);
            $this->assertEquals(['error' => 'Unauthorized'], $e->response);
        }
    }

    public function testConflictExceptionOn409(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(409, ['error' => 'Duplicate post', 'duplicate_post_id' => 'post-1']);

        try {
            $client->posts()->create('hello', ['prof-1']);
            $this->fail('Expected ConflictException');
        } catch (ConflictException $e) {
            $this->assertEquals(409, $e->statusCode);
            $this->assertEquals('post-1', $e->response['duplicate_post_id']);
        }
    }

    public function testIdempotencyKeyHeaderIsSent(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['id' => 'post-1']);

        $client->posts()->create('hello', ['prof-1'], idempotencyKey: '3f8b1c94-6a2d-4f0e-9d31-7c5e2a8b4f10');

        $this->assertEquals(
            '3f8b1c94-6a2d-4f0e-9d31-7c5e2a8b4f10',
            $this->lastRequest()->getHeaderLine('Idempotency-Key'),
        );
    }

    public function testIdempotencyKeyHeaderOmittedByDefault(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['id' => 'post-1']);

        $client->posts()->create('hello', ['prof-1']);

        $this->assertFalse($this->lastRequest()->hasHeader('Idempotency-Key'));
    }
}
