<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\PlatformParams\PlatformParams;
use PostProxy\Types\Post;
use PostProxy\Types\PostStats;
use PostProxy\Types\StatsResponse;

class Posts
{
    public function __construct(private readonly Client $client) {}

    public function list(
        ?int $page = null,
        ?int $perPage = null,
        ?string $status = null,
        ?array $platforms = null,
        string|\DateTimeInterface|null $scheduledAfter = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;
        if ($status !== null) $params['status'] = $status;
        if ($platforms !== null) $params['platforms'] = implode(',', $platforms);
        if ($scheduledAfter !== null) $params['scheduled_after'] = $this->formatTime($scheduledAfter);

        $result = $this->client->request('GET', '/posts', params: $params, profileGroupId: $profileGroupId);
        $posts = array_map(fn($p) => new Post($p), $result['data'] ?? []);

        return new PaginatedResponse(
            data: $posts,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }

    public function get(string $id, ?string $profileGroupId = null): Post
    {
        $result = $this->client->request('GET', "/posts/{$id}", profileGroupId: $profileGroupId);
        return new Post($result);
    }

    public function create(
        string $body,
        array $profiles,
        ?array $media = null,
        ?array $mediaFiles = null,
        PlatformParams|array|null $platforms = null,
        ?array $thread = null,
        string|\DateTimeInterface|null $scheduledAt = null,
        ?bool $draft = null,
        ?string $queueId = null,
        ?string $queuePriority = null,
        ?string $profileGroupId = null,
    ): Post {
        $hasFiles = $mediaFiles !== null && count($mediaFiles) > 0;
        $hasThreadFiles = $thread !== null && array_reduce($thread, function ($carry, $t) {
            return $carry || !empty($t['media_files']);
        }, false);

        if ($hasFiles || $hasThreadFiles) {
            $formData = ['post[body]' => $body];
            if ($scheduledAt !== null) {
                $formData['post[scheduled_at]'] = $this->formatTime($scheduledAt);
            }
            if ($draft !== null) {
                $formData['post[draft]'] = $draft ? 'true' : 'false';
            }

            $files = [];

            foreach ($profiles as $p) {
                $files[] = ['profiles[]', null, $p, 'text/plain'];
            }

            if ($media !== null) {
                foreach ($media as $m) {
                    $files[] = ['media[]', null, $m, 'text/plain'];
                }
            }

            if ($platforms !== null) {
                $paramsHash = $platforms instanceof PlatformParams ? $platforms->toArray() : $platforms;
                foreach ($paramsHash as $platform => $platformParams) {
                    foreach ($platformParams as $key => $value) {
                        $files[] = ["platforms[{$platform}][{$key}]", null, (string) $value, 'text/plain'];
                    }
                }
            }

            if ($mediaFiles !== null) {
                foreach ($mediaFiles as $path) {
                    $path = (string) $path;
                    $filename = basename($path);
                    $contentType = $this->mimeTypeFor($filename);
                    $io = fopen($path, 'rb');
                    $files[] = ['media[]', $filename, $io, $contentType];
                }
            }

            if ($thread !== null) {
                foreach ($thread as $i => $t) {
                    if (isset($t['body'])) {
                        $formData["thread[{$i}][body]"] = $t['body'];
                    }

                    if (!empty($t['media'])) {
                        foreach ($t['media'] as $m) {
                            $files[] = ["thread[{$i}][media][]", null, $m, 'text/plain'];
                        }
                    }

                    if (!empty($t['media_files'])) {
                        foreach ($t['media_files'] as $path) {
                            $path = (string) $path;
                            $filename = basename($path);
                            $contentType = $this->mimeTypeFor($filename);
                            $io = fopen($path, 'rb');
                            $files[] = ["thread[{$i}][media][]", $filename, $io, $contentType];
                        }
                    }
                }
            }

            $result = $this->client->request('POST', '/posts',
                data: $formData,
                files: $files,
                profileGroupId: $profileGroupId,
            );
        } else {
            $postPayload = ['body' => $body];
            if ($scheduledAt !== null) {
                $postPayload['scheduled_at'] = $this->formatTime($scheduledAt);
            }
            if ($draft !== null) {
                $postPayload['draft'] = $draft;
            }

            $jsonBody = ['post' => $postPayload, 'profiles' => $profiles];
            if ($platforms !== null) {
                $jsonBody['platforms'] = $platforms instanceof PlatformParams ? $platforms->toArray() : $platforms;
            }
            if ($media !== null) {
                $jsonBody['media'] = $media;
            }
            if ($thread !== null) {
                $jsonBody['thread'] = $thread;
            }
            if ($queueId !== null) {
                $jsonBody['queue_id'] = $queueId;
            }
            if ($queuePriority !== null) {
                $jsonBody['queue_priority'] = $queuePriority;
            }

            $result = $this->client->request('POST', '/posts', json: $jsonBody, profileGroupId: $profileGroupId);
        }

        return new Post($result);
    }

    public function publishDraft(string $id, ?string $profileGroupId = null): Post
    {
        $result = $this->client->request('POST', "/posts/{$id}/publish", profileGroupId: $profileGroupId);
        return new Post($result);
    }

    public function stats(
        array $postIds,
        ?array $profiles = null,
        string|\DateTimeInterface|null $from = null,
        string|\DateTimeInterface|null $to = null,
    ): StatsResponse {
        $params = ['post_ids' => implode(',', $postIds)];
        if ($profiles !== null) {
            $params['profiles'] = implode(',', $profiles);
        }
        if ($from !== null) {
            $params['from'] = $this->formatTime($from);
        }
        if ($to !== null) {
            $params['to'] = $this->formatTime($to);
        }

        $result = $this->client->request('GET', '/posts/stats', params: $params);

        $data = [];
        foreach ($result['data'] ?? [] as $postId => $postData) {
            $data[$postId] = new PostStats($postData);
        }

        return new StatsResponse($data);
    }

    public function delete(string $id, ?string $profileGroupId = null): DeleteResponse
    {
        $result = $this->client->request('DELETE', "/posts/{$id}", profileGroupId: $profileGroupId);
        return new DeleteResponse($result);
    }

    private function formatTime(string|\DateTimeInterface $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        return $value->format(\DateTimeInterface::ATOM);
    }

    private function mimeTypeFor(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => 'application/octet-stream',
        };
    }
}
