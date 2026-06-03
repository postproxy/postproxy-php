<?php

namespace PostProxy\Types;

class Reaction extends Model
{
    public ?string $senderExternalId = null;
    public ?string $emoji = null;
    public ?string $reaction = null;
    public mixed $at = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->at = self::parseTime($this->at);
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
