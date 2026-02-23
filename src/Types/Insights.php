<?php

namespace PostProxy\Types;

class Insights extends Model
{
    public ?int $impressions = null;
    public mixed $on = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->on = self::parseTime($this->on);
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
