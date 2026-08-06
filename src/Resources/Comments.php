<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\AcceptedResponse;
use PostProxy\Types\BulkComment;
use PostProxy\Types\Comment;
use PostProxy\Types\Message;
use PostProxy\Types\PaginatedResponse;

class Comments
{
    public function __construct(private readonly Client $client) {}

    /**
     * Lists a post's comments.
     *
     * `$from` and `$to` filter on when PostProxy received the comment
     * (`created_at`), not the platform's `posted_at`. They apply to top-level
     * comments — one in range brings its full `replies` array with it.
     */
    public function list(
        string $postId,
        string $profileId,
        ?int $page = null,
        ?int $perPage = null,
        ?string $from = null,
        ?string $to = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = ['profile_id' => $profileId];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;
        if ($from !== null) $params['from'] = $from;
        if ($to !== null) $params['to'] = $to;

        $result = $this->client->request('GET', "/posts/{$postId}/comments", params: $params, profileGroupId: $profileGroupId);
        $comments = array_map(fn($c) => new Comment($c), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $comments,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }

    /**
     * Lists comments across every post in the profile group.
     *
     * Flat: replies come back as their own entries linked by
     * `parentExternalId`, so `total` counts every comment. `$profiles` takes
     * profile IDs or network names, mixed.
     *
     * @param string[]|null $postIds
     * @param string[]|null $profiles
     */
    public function listAll(
        ?array $postIds = null,
        ?array $profiles = null,
        ?string $from = null,
        ?string $to = null,
        ?int $page = null,
        ?int $perPage = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($postIds !== null) $params['post_ids'] = implode(',', $postIds);
        if ($profiles !== null) $params['profiles'] = implode(',', $profiles);
        if ($from !== null) $params['from'] = $from;
        if ($to !== null) $params['to'] = $to;
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;

        $result = $this->client->request('GET', '/comments', params: $params ?: null, profileGroupId: $profileGroupId);
        $comments = array_map(fn($c) => new BulkComment($c), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $comments,
            total: $result['total'] ?? 0,
            page: $result['page'] ?? 0,
            perPage: $result['per_page'] ?? 0,
        );
    }

    public function get(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): Comment {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('GET', "/posts/{$postId}/comments/{$commentId}", params: $params, profileGroupId: $profileGroupId);
        return new Comment($result);
    }

    public function create(
        string $postId,
        string $profileId,
        string $text,
        ?string $parentId = null,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): Comment {
        $params = ['profile_id' => $profileId];
        $jsonBody = ['text' => $text];
        if ($parentId !== null) $jsonBody['parent_id'] = $parentId;

        $result = $this->client->request('POST', "/posts/{$postId}/comments", params: $params, json: $jsonBody, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new Comment($result);
    }

    public function delete(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('DELETE', "/posts/{$postId}/comments/{$commentId}", params: $params, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }

    public function hide(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/hide", params: $params, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }

    public function unhide(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/unhide", params: $params, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }

    public function like(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/like", params: $params, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }

    public function unlike(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/unlike", params: $params, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }

    public function privateReply(
        string $postId,
        string $commentId,
        string $profileId,
        string $text,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): Message {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/private_reply", params: $params, json: ['text' => $text], profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new Message($result);
    }
}
