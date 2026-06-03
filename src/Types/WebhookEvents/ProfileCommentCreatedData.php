<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class ProfileCommentCreatedData extends Model
{
    public ?string $id = null;
    public ?string $profileId = null;
    public ?string $platform = null;
    public ?string $placementId = null;
    public ?string $externalId = null;
    public ?string $parentExternalId = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $authorUsername = null;
    public ?string $authorAvatarUrl = null;
    public ?array $platformData = null;
    public ?string $postedAt = null;
    public ?string $createdAt = null;
}
