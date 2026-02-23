<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\ConnectionResponse;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\ProfileGroup;

class ProfileGroups
{
    public function __construct(private readonly Client $client) {}

    public function list(): ListResponse
    {
        $result = $this->client->request('GET', '/profile_groups');
        $groups = array_map(fn($g) => new ProfileGroup($g), $result['data'] ?? []);
        return new ListResponse(data: $groups);
    }

    public function get(string $id): ProfileGroup
    {
        $result = $this->client->request('GET', "/profile_groups/{$id}");
        return new ProfileGroup($result);
    }

    public function create(string $name): ProfileGroup
    {
        $result = $this->client->request('POST', '/profile_groups', json: ['name' => $name]);
        return new ProfileGroup($result);
    }

    public function delete(string $id): DeleteResponse
    {
        $result = $this->client->request('DELETE', "/profile_groups/{$id}");
        return new DeleteResponse($result);
    }

    public function initializeConnection(string $id, string $platform, string $redirectUrl): ConnectionResponse
    {
        $result = $this->client->request('POST', "/profile_groups/{$id}/initialize_connection", json: [
            'platform' => $platform,
            'redirect_url' => $redirectUrl,
        ]);
        return new ConnectionResponse($result);
    }
}
