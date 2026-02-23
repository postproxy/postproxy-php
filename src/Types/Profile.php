<?php

namespace PostProxy\Types;

class Profile extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $status = null;
    public ?string $platform = null;
    public ?string $profileGroupId = null;
    public mixed $expiresAt = null;
    public int $postCount = 0;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->expiresAt = self::parseTime($this->expiresAt);
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
