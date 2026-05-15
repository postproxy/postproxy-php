<?php

namespace PostProxy\Types;

class ProfileComment extends Model
{
    public ?string $id = null;
    public ?string $externalId = null;
    public ?string $parentExternalId = null;
    public ?string $placementId = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $authorUsername = null;
    public ?string $authorAvatarUrl = null;
    public mixed $platformData = null;
    public mixed $postedAt = null;
    public mixed $createdAt = null;
    /** @var ProfileComment[] */
    public array $replies = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->postedAt = self::parseTime($this->postedAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->replies = array_map(function ($r) {
            if ($r instanceof ProfileComment) {
                return $r;
            }
            return new ProfileComment($r);
        }, $this->replies ?? []);
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
