<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\IceBreaker;
use PostProxy\Types\IceBreakersResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\Placement;
use PostProxy\Types\Profile;
use PostProxy\Types\ProfileStatsResponse;
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

    /**
     * Fetch profile stats timeseries.
     *
     * `$placementId` is required for facebook, linkedin, and telegram profiles.
     */
    public function getProfileStats(
        string $id,
        ?string $placementId = null,
        ?string $from = null,
        ?string $to = null,
        ?string $profileGroupId = null,
    ): ProfileStatsResponse {
        $params = [];
        if ($placementId !== null) $params['placement_id'] = $placementId;
        if ($from !== null) $params['from'] = $from;
        if ($to !== null) $params['to'] = $to;

        $result = $this->client->request(
            'GET',
            "/profiles/{$id}/stats",
            params: $params ?: null,
            profileGroupId: $profileGroupId,
        );
        return new ProfileStatsResponse($result);
    }

    /**
     * Moves a placement (e.g. a Facebook Page or Telegram channel) to another
     * profile group. `$placementId` is the placement's external ID as returned
     * by placements().
     */
    public function assignPlacementToGroup(
        string $id,
        string $placementId,
        string $targetProfileGroupId,
        ?string $profileGroupId = null,
    ): Placement {
        $result = $this->client->request(
            'PATCH',
            "/profiles/{$id}/assign_placement_to_group",
            json: [
                'placement_id' => $placementId,
                'target_profile_group_id' => $targetProfileGroupId,
            ],
            profileGroupId: $profileGroupId,
        );
        return new Placement($result);
    }

    /**
     * Lists DM ice breakers. Supported for Instagram profiles only.
     */
    public function iceBreakers(string $id, ?string $profileGroupId = null): IceBreakersResponse
    {
        $result = $this->client->request('GET', "/profiles/{$id}/ice_breakers", profileGroupId: $profileGroupId);
        return new IceBreakersResponse($result);
    }

    /**
     * Replaces the DM ice breakers for a profile (1-4 items).
     *
     * @param array<IceBreaker|array{question: string, payload: string}> $iceBreakers
     */
    public function setIceBreakers(string $id, array $iceBreakers, ?string $profileGroupId = null): SuccessResponse
    {
        $items = array_map(
            fn($ib) => $ib instanceof IceBreaker
                ? ['question' => $ib->question, 'payload' => $ib->payload]
                : $ib,
            $iceBreakers,
        );
        $result = $this->client->request(
            'POST',
            "/profiles/{$id}/ice_breakers",
            json: ['ice_breakers' => $items],
            profileGroupId: $profileGroupId,
        );
        return new SuccessResponse($result);
    }

    public function deleteIceBreakers(string $id, ?string $profileGroupId = null): SuccessResponse
    {
        $result = $this->client->request('DELETE', "/profiles/{$id}/ice_breakers", profileGroupId: $profileGroupId);
        return new SuccessResponse($result);
    }

    public function delete(string $id, ?string $profileGroupId = null): SuccessResponse
    {
        $result = $this->client->request('DELETE', "/profiles/{$id}", profileGroupId: $profileGroupId);
        return new SuccessResponse($result);
    }
}
