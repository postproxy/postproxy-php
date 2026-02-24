<?php

namespace PostProxy\Types;

class PlatformStats extends Model
{
    public ?string $profileId = null;
    public ?string $platform = null;
    public array $records = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->records = array_map(
            fn($r) => $r instanceof StatsRecord ? $r : new StatsRecord($r),
            $this->records ?? [],
        );
    }
}
