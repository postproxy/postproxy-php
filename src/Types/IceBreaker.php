<?php

namespace PostProxy\Types;

/**
 * Instagram DM ice breaker (FAQ prompt shown when a user opens a chat).
 */
class IceBreaker extends Model
{
    public ?string $question = null;
    public ?string $payload = null;
}
