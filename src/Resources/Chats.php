<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\Chat;
use PostProxy\Types\PaginatedResponse;

class Chats
{
    public function __construct(private readonly Client $client) {}

    public function list(
        string $profileId,
        ?int $page = null,
        ?int $perPage = null,
        string|\DateTimeInterface|null $before = null,
        string|\DateTimeInterface|null $after = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;
        if ($before !== null) $params['before'] = $this->formatTime($before);
        if ($after !== null) $params['after'] = $this->formatTime($after);

        $result = $this->client->request('GET', "/profiles/{$profileId}/chats", params: $params, profileGroupId: $profileGroupId);
        $chats = array_map(fn($c) => new Chat($c), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $chats,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }

    public function create(
        string $profileId,
        string $participantExternalId,
        ?string $participantUsername = null,
        ?string $participantName = null,
        ?string $profileGroupId = null,
    ): Chat {
        $jsonBody = ['participant_external_id' => $participantExternalId];
        if ($participantUsername !== null) $jsonBody['participant_username'] = $participantUsername;
        if ($participantName !== null) $jsonBody['participant_name'] = $participantName;

        $result = $this->client->request('POST', "/profiles/{$profileId}/chats", json: $jsonBody, profileGroupId: $profileGroupId);
        return new Chat($result);
    }

    public function get(string $chatId, ?string $profileGroupId = null): Chat
    {
        $result = $this->client->request('GET', "/chats/{$chatId}", profileGroupId: $profileGroupId);
        return new Chat($result);
    }

    public function archive(string $chatId, ?string $profileGroupId = null): Chat
    {
        $result = $this->client->request('POST', "/chats/{$chatId}/archive", profileGroupId: $profileGroupId);
        return new Chat($result);
    }

    public function unarchive(string $chatId, ?string $profileGroupId = null): Chat
    {
        $result = $this->client->request('DELETE', "/chats/{$chatId}/archive", profileGroupId: $profileGroupId);
        return new Chat($result);
    }

    private function formatTime(string|\DateTimeInterface $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        return $value->format(\DateTimeInterface::ATOM);
    }
}
