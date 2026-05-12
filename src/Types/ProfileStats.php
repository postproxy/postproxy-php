<?php

namespace PostProxy\Types;

class ProfileStats extends Model
{
    public ?string $profileId = null;
    public ?string $platform = null;
    public ?string $placementId = null;
    /** @var StatsRecord[] */
    public array $records = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->records = array_map(
            fn($r) => $r instanceof StatsRecord ? $r : new StatsRecord($r),
            $this->records,
        );
    }
}
