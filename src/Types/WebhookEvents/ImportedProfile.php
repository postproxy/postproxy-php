<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Model;

class ImportedProfile extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $platform = null;
}
