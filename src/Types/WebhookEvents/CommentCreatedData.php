<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class CommentCreatedData extends Model
{
    public ?string $id = null;
    public ?string $postId = null;
    public ?string $platformPostId = null;
    public ?string $platform = null;
    public ?string $externalId = null;
    public ?string $parentExternalId = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $authorExternalId = null;
    public ?string $authorName = null;
    public ?string $authorUsername = null;
    public ?string $authorAvatarUrl = null;
    public int $likeCount = 0;
    public int $replyCount = 0;
    public bool $isHidden = false;
    public ?string $permalink = null;
    public ?array $platformData = null;
    public ?string $postedAt = null;
    public ?string $createdAt = null;
}
