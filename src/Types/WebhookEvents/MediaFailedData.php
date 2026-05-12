<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class MediaFailedData extends Model
{
    public ?string $id = null;
    public ?string $postId = null;
    public ?string $contentType = null;
    public ?string $status = null;
    public ?string $errorMessage = null;
}
