<?php

namespace PostProxy\Types;

/**
 * A record of one post pull for a profile — the sync fired when the profile
 * connects, the recurring poll, or a backfill.
 */
class PostSync extends Model
{
    public ?string $id = null;
    public ?string $profileId = null;
    public ?string $kind = null;
    /** connect | scheduled | backfill */
    public ?string $trigger = null;
    /** pending | running | completed | failed */
    public ?string $status = null;
    public mixed $startedAt = null;
    public mixed $completedAt = null;
    public int $postsSeen = 0;
    /**
     * Posts that were new and got created — lower than `postsSeen` whenever the
     * run re-read posts you already have.
     */
    public int $postsImported = 0;
    public mixed $backfillFrom = null;
    /** Publish date of the oldest post the run reached. */
    public mixed $oldestPostedAt = null;
    public ?string $error = null;
    public mixed $createdAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->startedAt = self::parseTime($this->startedAt);
        $this->completedAt = self::parseTime($this->completedAt);
        $this->backfillFrom = self::parseTime($this->backfillFrom);
        $this->oldestPostedAt = self::parseTime($this->oldestPostedAt);
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
