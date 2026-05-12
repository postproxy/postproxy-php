<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class PostImportedData extends Model
{
    public ?string $id = null;
    public ?string $body = null;
    public ?string $source = null;
    public ?string $postedAt = null;
    public ?string $createdAt = null;
    public ?string $platform = null;
    public mixed $profile = null;
    public ?string $platformPostId = null;
    public ?string $publicId = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->profile)) {
            $this->profile = new ImportedProfile($this->profile);
        }
    }
}
