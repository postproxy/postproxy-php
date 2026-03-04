<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\Webhook;
use PostProxy\Types\WebhookDelivery;

class Webhooks
{
    public function __construct(private readonly Client $client) {}

    public function list(): ListResponse
    {
        $result = $this->client->request('GET', '/webhooks');
        $webhooks = array_map(fn($w) => new Webhook($w), $result['data'] ?? []);
        return new ListResponse(data: $webhooks);
    }

    public function get(string $id): Webhook
    {
        $result = $this->client->request('GET', "/webhooks/{$id}");
        return new Webhook($result);
    }

    public function create(
        string $url,
        array $events,
        ?string $description = null,
    ): Webhook {
        $jsonBody = ['url' => $url, 'events' => $events];
        if ($description !== null) {
            $jsonBody['description'] = $description;
        }

        $result = $this->client->request('POST', '/webhooks', json: $jsonBody);
        return new Webhook($result);
    }

    public function update(
        string $id,
        ?string $url = null,
        ?array $events = null,
        ?bool $enabled = null,
        ?string $description = null,
    ): Webhook {
        $jsonBody = [];
        if ($url !== null) $jsonBody['url'] = $url;
        if ($events !== null) $jsonBody['events'] = $events;
        if ($enabled !== null) $jsonBody['enabled'] = $enabled;
        if ($description !== null) $jsonBody['description'] = $description;

        $result = $this->client->request('PATCH', "/webhooks/{$id}", json: $jsonBody);
        return new Webhook($result);
    }

    public function delete(string $id): DeleteResponse
    {
        $result = $this->client->request('DELETE', "/webhooks/{$id}");
        return new DeleteResponse($result);
    }

    public function deliveries(
        string $id,
        ?int $page = null,
        ?int $perPage = null,
    ): PaginatedResponse {
        $params = [];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;

        $result = $this->client->request('GET', "/webhooks/{$id}/deliveries", params: $params);
        $deliveries = array_map(fn($d) => new WebhookDelivery($d), $result['data'] ?? []);

        return new PaginatedResponse(
            data: $deliveries,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }
}
