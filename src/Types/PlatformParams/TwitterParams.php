<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class TwitterParams extends Model
{
    public ?string $format = null;

    /**
     * Required when $format is "poll": 2-4 options, max 25 characters each.
     *
     * @var string[]|null
     */
    public ?array $pollOptions = null;

    /** Required when $format is "poll": 5 to 10080 minutes (7 days). */
    public ?int $pollDurationMinutes = null;
}
