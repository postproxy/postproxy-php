<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\ErrorDetails;
use PostProxy\Types\Model;

class PlatformPostData extends Model
{
    public ?string $id = null;
    public ?string $postId = null;
    public ?string $platform = null;
    public ?string $profileId = null;
    public ?string $profileName = null;
    public ?string $status = null;
    public ?string $error = null;
    public mixed $errorDetails = null;
    public ?string $platformId = null;
    public ?array $insights = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->errorDetails)) {
            $this->errorDetails = new ErrorDetails($this->errorDetails);
        }
    }
}
