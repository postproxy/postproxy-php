<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class ProfileStatsData extends Model
{
    public ?string $profileId = null;
    public ?string $platform = null;
    public ?string $placementId = null;
    public array $stats = [];
    public ?string $recordedAt = null;
}
