<?php

namespace PostProxy\Types;

class Chat extends Model
{
    public ?string $id = null;
    public ?string $profileId = null;
    public ?string $platform = null;
    public ?string $participantExternalId = null;
    public ?string $participantUsername = null;
    public ?string $participantName = null;
    public ?string $participantAvatarUrl = null;
    public ?string $externalConversationId = null;
    public mixed $lastInboundAt = null;
    public mixed $lastOutboundAt = null;
    public mixed $lastMessageAt = null;
    public mixed $metadata = null;
    public ?bool $archived = null;
    public mixed $createdAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->lastInboundAt = self::parseTime($this->lastInboundAt);
        $this->lastOutboundAt = self::parseTime($this->lastOutboundAt);
        $this->lastMessageAt = self::parseTime($this->lastMessageAt);
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
