<?php

namespace PostProxy\Types;

class Comment extends Model
{
    public ?string $id = null;
    public ?string $externalId = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $authorUsername = null;
    public ?string $authorAvatarUrl = null;
    public ?string $authorExternalId = null;
    public ?string $parentExternalId = null;
    public int $likeCount = 0;
    public bool $isHidden = false;
    public ?string $permalink = null;
    public mixed $platformData = null;
    public mixed $postedAt = null;
    public mixed $createdAt = null;
    public array $replies = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->postedAt = self::parseTime($this->postedAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->replies = array_map(function ($r) {
            if ($r instanceof Comment) {
                return $r;
            }
            return new Comment($r);
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
