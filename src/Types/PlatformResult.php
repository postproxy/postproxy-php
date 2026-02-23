<?php

namespace PostProxy\Types;

class PlatformResult extends Model
{
    public ?string $platform = null;
    public ?string $status = null;
    public ?array $params = null;
    public ?string $error = null;
    public mixed $attemptedAt = null;
    public mixed $insights = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->attemptedAt = self::parseTime($this->attemptedAt);
        if (is_array($this->insights)) {
            $this->insights = new Insights($this->insights);
        }
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
