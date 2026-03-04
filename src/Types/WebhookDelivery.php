<?php

namespace PostProxy\Types;

class WebhookDelivery extends Model
{
    public ?string $id = null;
    public ?string $eventId = null;
    public ?string $eventType = null;
    public ?int $responseStatus = null;
    public ?int $attemptNumber = null;
    public ?bool $success = null;
    public mixed $attemptedAt = null;
    public mixed $createdAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->attemptedAt = self::parseTime($this->attemptedAt);
        $this->createdAt = self::parseTime($this->createdAt);
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
