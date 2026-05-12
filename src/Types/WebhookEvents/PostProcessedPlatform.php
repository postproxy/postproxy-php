<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class PostProcessedPlatform extends Model
{
    public ?string $id = null;
    public ?string $platform = null;
    public ?string $name = null;
}
