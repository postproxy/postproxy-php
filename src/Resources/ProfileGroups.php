<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\BlueskyConnectionResponse;
use PostProxy\Types\ConnectionResponse;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\ProfileGroup;
use PostProxy\Types\TelegramConnectionResponse;

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

    /**
     * Initialize an OAuth connection. BlueSky and Telegram have dedicated
     * helpers (`connectBluesky`, `connectTelegram`).
     */
    public function initializeConnection(string $id, string $platform, ?string $redirectUrl = null): ConnectionResponse
    {
        $body = ['platform' => $platform];
        if ($redirectUrl !== null) {
            $body['redirect_url'] = $redirectUrl;
        }
        $result = $this->client->request('POST', "/profile_groups/{$id}/initialize_connection", json: $body);
        return new ConnectionResponse($result);
    }

    public function connectBluesky(string $id, string $identifier, string $appPassword): BlueskyConnectionResponse
    {
        $result = $this->client->request('POST', "/profile_groups/{$id}/initialize_connection", json: [
            'platform' => 'bluesky',
            'identifier' => $identifier,
            'app_password' => $appPassword,
        ]);
        return new BlueskyConnectionResponse($result);
    }

    /**
     * After this call, poll `profiles->placements($profileId)` until non-empty —
     * the bot must be added as administrator to a Telegram channel first.
     */
    public function connectTelegram(string $id, string $botToken): TelegramConnectionResponse
    {
        $result = $this->client->request('POST', "/profile_groups/{$id}/initialize_connection", json: [
            'platform' => 'telegram',
            'bot_token' => $botToken,
        ]);
        return new TelegramConnectionResponse($result);
    }
}
