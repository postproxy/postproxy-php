<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class ProfileEventData extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $platform = null;
    public ?string $profileGroupId = null;
    public ?string $status = null;
    public ?string $uid = null;
    public ?string $username = null;
}
