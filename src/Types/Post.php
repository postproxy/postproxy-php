<?php

namespace PostProxy\Types;

class Post extends Model
{
    public ?string $id = null;
    public ?string $body = null;
    public ?string $status = null;
    public mixed $scheduledAt = null;
    public mixed $createdAt = null;
    public array $platforms = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->scheduledAt = self::parseTime($this->scheduledAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->platforms = array_map(function ($p) {
            if ($p instanceof PlatformResult) {
                return $p;
            }
            return new PlatformResult($p);
        }, $this->platforms ?? []);
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
