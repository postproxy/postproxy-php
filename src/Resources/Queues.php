<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\NextSlotResponse;
use PostProxy\Types\Queue;

class Queues
{
    public function __construct(private readonly Client $client) {}

    public function list(?string $profileGroupId = null): ListResponse
    {
        $result = $this->client->request('GET', '/post_queues', profileGroupId: $profileGroupId);
        $queues = array_map(fn($q) => new Queue($q), $result['data'] ?? []);
        return new ListResponse(data: $queues);
    }

    public function get(string $id): Queue
    {
        $result = $this->client->request('GET', "/post_queues/{$id}");
        return new Queue($result);
    }

    public function nextSlot(string $id): NextSlotResponse
    {
        $result = $this->client->request('GET', "/post_queues/{$id}/next_slot");
        return new NextSlotResponse($result);
    }

    public function create(
        string $name,
        string $profileGroupId,
        ?string $description = null,
        ?string $timezone = null,
        ?int $jitter = null,
        ?array $timeslots = null,
    ): Queue {
        $postQueue = ['name' => $name];
        if ($description !== null) $postQueue['description'] = $description;
        if ($timezone !== null) $postQueue['timezone'] = $timezone;
        if ($jitter !== null) $postQueue['jitter'] = $jitter;
        if ($timeslots !== null) $postQueue['queue_timeslots_attributes'] = $timeslots;

        $jsonBody = [
            'profile_group_id' => $profileGroupId,
            'post_queue' => $postQueue,
        ];

        $result = $this->client->request('POST', '/post_queues', json: $jsonBody);
        return new Queue($result);
    }

    public function update(
        string $id,
        ?string $name = null,
        ?string $description = null,
        ?string $timezone = null,
        ?bool $enabled = null,
        ?int $jitter = null,
        ?array $timeslots = null,
    ): Queue {
        $postQueue = [];
        if ($name !== null) $postQueue['name'] = $name;
        if ($description !== null) $postQueue['description'] = $description;
        if ($timezone !== null) $postQueue['timezone'] = $timezone;
        if ($enabled !== null) $postQueue['enabled'] = $enabled;
        if ($jitter !== null) $postQueue['jitter'] = $jitter;
        if ($timeslots !== null) $postQueue['queue_timeslots_attributes'] = $timeslots;

        $jsonBody = ['post_queue' => $postQueue];

        $result = $this->client->request('PATCH', "/post_queues/{$id}", json: $jsonBody);
        return new Queue($result);
    }

    public function delete(string $id): DeleteResponse
    {
        $result = $this->client->request('DELETE', "/post_queues/{$id}");
        return new DeleteResponse($result);
    }
}
