<?php

namespace PostProxy\Types;

class StatsRecord extends Model
{
    public array $stats = [];
    public mixed $recordedAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->recordedAt = self::parseTime($this->recordedAt);
    }

    private static function parseTime(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        return new \DateTimeImmutable((string) $value);
    }
}
