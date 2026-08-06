<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\AcceptedResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\ProfileComment;

class ProfileComments
{
    public function __construct(private readonly Client $client) {}

    public function list(
        string $profileId,
        ?string $placementId = null,
        ?int $page = null,
        ?int $perPage = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($placementId !== null) $params['placement_id'] = $placementId;
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;

        $result = $this->client->request('GET', "/profiles/{$profileId}/comments", params: $params, profileGroupId: $profileGroupId);
        $comments = array_map(fn($c) => new ProfileComment($c), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $comments,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }

    public function get(
        string $profileId,
        string $commentId,
        ?string $profileGroupId = null,
    ): ProfileComment {
        $result = $this->client->request('GET', "/profiles/{$profileId}/comments/{$commentId}", profileGroupId: $profileGroupId);
        return new ProfileComment($result);
    }

    public function create(
        string $profileId,
        string $parentId,
        string $text,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): ProfileComment {
        $result = $this->client->request(
            'POST',
            "/profiles/{$profileId}/comments",
            json: ['parent_id' => $parentId, 'text' => $text],
            profileGroupId: $profileGroupId,
            idempotencyKey: $idempotencyKey,
        );
        return new ProfileComment($result);
    }

    public function delete(
        string $profileId,
        string $commentId,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): AcceptedResponse {
        $result = $this->client->request('DELETE', "/profiles/{$profileId}/comments/{$commentId}", profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new AcceptedResponse($result);
    }
}
