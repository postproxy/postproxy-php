<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\IceBreaker;
use PostProxy\Types\IceBreakersResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\Placement;
use PostProxy\Types\PostSync;
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
        ?string $idempotencyKey = null,
    ): Placement {
        $result = $this->client->request(
            'PATCH',
            "/profiles/{$id}/assign_placement_to_group",
            json: [
                'placement_id' => $placementId,
                'target_profile_group_id' => $targetProfileGroupId,
            ],
            profileGroupId: $profileGroupId,
            idempotencyKey: $idempotencyKey,
        );
        return new Placement($result);
    }

    /**
     * Imports older posts from the platform.
     *
     * Walks the profile's feed backwards from the newest post until it reaches
     * `$from` or the platform stops returning posts. Runs in the background —
     * poll postSync() with the returned id for progress. Only one backfill runs
     * per profile; starting a second throws a ConflictException carrying the
     * running one's `profile_sync_id`.
     */
    public function backfillPosts(
        string $id,
        string $from,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): PostSync {
        $result = $this->client->request(
            'POST',
            "/profiles/{$id}/backfill_posts",
            json: ['from' => $from],
            profileGroupId: $profileGroupId,
            idempotencyKey: $idempotencyKey,
        );
        return new PostSync($result);
    }

    /**
     * Lists post sync runs, newest first. Runs are kept for 30 days.
     */
    public function postSyncs(
        string $id,
        ?string $trigger = null,
        ?string $status = null,
        ?int $page = null,
        ?int $perPage = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($trigger !== null) $params['trigger'] = $trigger;
        if ($status !== null) $params['status'] = $status;
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;

        $result = $this->client->request(
            'GET',
            "/profiles/{$id}/post_syncs",
            params: $params ?: null,
            profileGroupId: $profileGroupId,
        );

        $syncs = array_map(fn($s) => new PostSync($s), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $syncs,
            total: $result['total'] ?? 0,
            page: $result['page'] ?? 0,
            perPage: $result['per_page'] ?? 0,
        );
    }

    /**
     * Fetches a single run. Poll this to follow a backfill to completion — the
     * run is finished when `status` is `completed` or `failed`.
     */
    public function postSync(string $id, string $postSyncId, ?string $profileGroupId = null): PostSync
    {
        $result = $this->client->request(
            'GET',
            "/profiles/{$id}/post_syncs/{$postSyncId}",
            profileGroupId: $profileGroupId,
        );
        return new PostSync($result);
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
    public function setIceBreakers(
        string $id,
        array $iceBreakers,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): SuccessResponse {
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
            idempotencyKey: $idempotencyKey,
        );
        return new SuccessResponse($result);
    }

    public function deleteIceBreakers(
        string $id,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): SuccessResponse {
        $result = $this->client->request(
            'DELETE',
            "/profiles/{$id}/ice_breakers",
            profileGroupId: $profileGroupId,
            idempotencyKey: $idempotencyKey,
        );
        return new SuccessResponse($result);
    }

    public function delete(
        string $id,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): SuccessResponse {
        $result = $this->client->request(
            'DELETE',
            "/profiles/{$id}",
            profileGroupId: $profileGroupId,
            idempotencyKey: $idempotencyKey,
        );
        return new SuccessResponse($result);
    }
}
