<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class PostProcessedData extends Model
{
    public ?string $id = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $scheduledAt = null;
    public ?string $createdAt = null;
    /** @var PostProcessedPlatform[] */
    public array $platforms = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->platforms = array_map(
            fn($p) => $p instanceof PostProcessedPlatform ? $p : new PostProcessedPlatform($p),
            $this->platforms,
        );
    }
}
