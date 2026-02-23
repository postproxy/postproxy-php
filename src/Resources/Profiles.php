<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\ListResponse;
use PostProxy\Types\Placement;
use PostProxy\Types\Profile;
use PostProxy\Types\SuccessResponse;

class Profiles
{
    public function __construct(private readonly Client $client) {}

    public function list(?string $profileGroupId = null): ListResponse
    {
        $result = $this->client->request('GET', '/profiles', profileGroupId: $profileGroupId);
        $profiles = array_map(fn($p) => new Profile($p), $result['data'] ?? []);
        return new ListResponse(data: $profiles);
    }

    public function get(string $id, ?string $profileGroupId = null): Profile
    {
        $result = $this->client->request('GET', "/profiles/{$id}", profileGroupId: $profileGroupId);
        return new Profile($result);
    }

    public function placements(string $id, ?string $profileGroupId = null): ListResponse
    {
        $result = $this->client->request('GET', "/profiles/{$id}/placements", profileGroupId: $profileGroupId);
        $items = array_map(fn($p) => new Placement($p), $result['data'] ?? []);
        return new ListResponse(data: $items);
    }

    public function delete(string $id, ?string $profileGroupId = null): SuccessResponse
    {
        $result = $this->client->request('DELETE', "/profiles/{$id}", profileGroupId: $profileGroupId);
        return new SuccessResponse($result);
    }
}
