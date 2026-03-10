<?php

namespace PostProxy;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use PostProxy\Exceptions\AuthenticationException;
use PostProxy\Exceptions\BadRequestException;
use PostProxy\Exceptions\NotFoundException;
use PostProxy\Exceptions\PostProxyException;
use PostProxy\Exceptions\ValidationException;
use PostProxy\Resources\Posts;
use PostProxy\Resources\Profiles;
use PostProxy\Resources\ProfileGroups;
use PostProxy\Resources\Webhooks;
use PostProxy\Resources\Queues;

class Client
{
    private ClientInterface $httpClient;
    private ?Posts $posts = null;
    private ?Profiles $profiles = null;
    private ?ProfileGroups $profileGroups = null;
    private ?Webhooks $webhooks = null;
    private ?Queues $queues = null;

    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = Constants::DEFAULT_BASE_URL,
        public readonly ?string $profileGroupId = null,
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new GuzzleClient([
            'base_uri' => $this->baseUrl,
        ]);
    }

    public function posts(): Posts
    {
        return $this->posts ??= new Posts($this);
    }

    public function profiles(): Profiles
    {
        return $this->profiles ??= new Profiles($this);
    }

    public function profileGroups(): ProfileGroups
    {
        return $this->profileGroups ??= new ProfileGroups($this);
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks ??= new Webhooks($this);
    }

    public function queues(): Queues
    {
        return $this->queues ??= new Queues($this);
    }

    /**
     * @param array<string, mixed>|null $params Query parameters
     * @param array<string, mixed>|null $json JSON body
     * @param array<string, string>|null $data Form data fields (for multipart)
     * @param array<array>|null $files File upload parts [[field, filename, path, content_type], ...]
     */
    public function request(
        string $method,
        string $path,
        ?array $params = null,
        ?array $json = null,
        ?array $data = null,
        ?array $files = null,
        ?string $profileGroupId = null,
    ): ?array {
        $url = "/api{$path}";

        $query = [];
        $pgid = $profileGroupId ?? $this->profileGroupId;
        if ($pgid !== null) {
            $query['profile_group_id'] = $pgid;
        }
        if ($params !== null) {
            $query = array_merge($query, $params);
        }

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        if ($files !== null) {
            $multipart = [];

            if ($data !== null) {
                foreach ($data as $key => $value) {
                    $multipart[] = [
                        'name' => $key,
                        'contents' => (string) $value,
                    ];
                }
            }

            foreach ($files as $file) {
                [$field, $filename, $content, $contentType] = $file;
                if (is_resource($content) || $content instanceof \Psr\Http\Message\StreamInterface) {
                    $part = [
                        'name' => $field,
                        'contents' => $content,
                        'headers' => ['Content-Type' => $contentType],
                    ];
                    if ($filename !== null) {
                        $part['filename'] = $filename;
                    }
                    $multipart[] = $part;
                } else {
                    $multipart[] = [
                        'name' => $field,
                        'contents' => (string) $content,
                    ];
                }
            }

            $response = $this->httpClient->request($method, $url, [
                'multipart' => $multipart,
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
                'http_errors' => false,
            ]);
        } else {
            $options = [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
            ];

            if ($json !== null) {
                $options['body'] = json_encode($json);
            }

            $options['http_errors'] = false;
            $response = $this->httpClient->request($method, $url, $options);
        }

        return $this->handleResponse($response);
    }

    private function handleResponse(\Psr\Http\Message\ResponseInterface $response): ?array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode >= 200 && $statusCode < 300) {
            if ($statusCode === 204 || $body === '') {
                return null;
            }
            return json_decode($body, true);
        }

        $parsed = $this->parseErrorBody($body);
        $message = $parsed['message'] ?? $parsed['error'] ?? 'Unknown error';

        throw match ($statusCode) {
            401 => new AuthenticationException($message, $statusCode, $parsed),
            404 => new NotFoundException($message, $statusCode, $parsed),
            422 => new ValidationException($message, $statusCode, $parsed),
            400 => new BadRequestException($message, $statusCode, $parsed),
            default => new PostProxyException($message, $statusCode, $parsed),
        };
    }

    private function parseErrorBody(string $body): array
    {
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        return ['error' => $body];
    }
}
