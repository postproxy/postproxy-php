<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\AcceptedResponse;
use PostProxy\Types\Comment;
use PostProxy\Types\PaginatedResponse;

class Comments
{
    public function __construct(private readonly Client $client) {}

    public function list(
        string $postId,
        string $profileId,
        ?int $page = null,
        ?int $perPage = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = ['profile_id' => $profileId];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;

        $result = $this->client->request('GET', "/posts/{$postId}/comments", params: $params, profileGroupId: $profileGroupId);
        $comments = array_map(fn($c) => new Comment($c), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $comments,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
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
    ): Comment {
        $params = ['profile_id' => $profileId];
        $jsonBody = ['text' => $text];
        if ($parentId !== null) $jsonBody['parent_id'] = $parentId;

        $result = $this->client->request('POST', "/posts/{$postId}/comments", params: $params, json: $jsonBody, profileGroupId: $profileGroupId);
        return new Comment($result);
    }

    public function delete(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('DELETE', "/posts/{$postId}/comments/{$commentId}", params: $params, profileGroupId: $profileGroupId);
        return new AcceptedResponse($result);
    }

    public function hide(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/hide", params: $params, profileGroupId: $profileGroupId);
        return new AcceptedResponse($result);
    }

    public function unhide(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/unhide", params: $params, profileGroupId: $profileGroupId);
        return new AcceptedResponse($result);
    }

    public function like(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/like", params: $params, profileGroupId: $profileGroupId);
        return new AcceptedResponse($result);
    }

    public function unlike(
        string $postId,
        string $commentId,
        string $profileId,
        ?string $profileGroupId = null,
    ): AcceptedResponse {
        $params = ['profile_id' => $profileId];
        $result = $this->client->request('POST', "/posts/{$postId}/comments/{$commentId}/unlike", params: $params, profileGroupId: $profileGroupId);
        return new AcceptedResponse($result);
    }
}
