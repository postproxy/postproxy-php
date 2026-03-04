<?php

namespace PostProxy\Types;

class Webhook extends Model
{
    public ?string $id = null;
    public ?string $url = null;
    public array $events = [];
    public ?bool $enabled = null;
    public ?string $description = null;
    public ?string $secret = null;
    public mixed $createdAt = null;
    public mixed $updatedAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->updatedAt = self::parseTime($this->updatedAt);
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
