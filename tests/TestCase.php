<?php

namespace PostProxy\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PostProxy\Client;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const BASE_URL = 'https://api.postproxy.dev';

    protected MockHandler $mockHandler;
    protected array $requestHistory = [];

    protected function mockClient(?string $profileGroupId = null): Client
    {
        $this->mockHandler = new MockHandler();
        $this->requestHistory = [];

        $handlerStack = HandlerStack::create($this->mockHandler);
        $handlerStack->push(Middleware::history($this->requestHistory));

        $httpClient = new GuzzleClient([
            'handler' => $handlerStack,
            'base_uri' => self::BASE_URL,
        ]);

        return new Client(
            apiKey: 'test-key',
            baseUrl: self::BASE_URL,
            profileGroupId: $profileGroupId,
            httpClient: $httpClient,
        );
    }

    protected function queueResponse(int $status = 200, array $body = []): void
    {
        $this->mockHandler->append(new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($body),
        ));
    }

    protected function lastRequest(): \GuzzleHttp\Psr7\Request
    {
        return end($this->requestHistory)['request'];
    }

    protected function lastRequestBody(): array
    {
        return json_decode((string) $this->lastRequest()->getBody(), true);
    }

    protected function lastRequestUri(): string
    {
        return (string) $this->lastRequest()->getUri();
    }
}
