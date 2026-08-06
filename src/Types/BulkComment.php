<?php

namespace PostProxy\Types;

/**
 * A comment from Comments::listAll().
 *
 * Flat: replies are their own entries linked to their parent by
 * `parentExternalId` rather than nested under `replies`.
 */
class BulkComment extends Model
{
    public ?string $postId = null;
    public ?string $profileId = null;
    public ?string $platform = null;
    public ?string $id = null;
    public ?string $externalId = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $authorUsername = null;
    public ?string $authorAvatarUrl = null;
    public ?string $authorExternalId = null;
    public mixed $metadata = null;
    public ?string $parentExternalId = null;
    public int $likeCount = 0;
    public bool $isHidden = false;
    public ?string $permalink = null;
    public mixed $platformData = null;
    /** @var Attachment[] */
    public array $attachments = [];
    public mixed $postedAt = null;
    public mixed $createdAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->postedAt = self::parseTime($this->postedAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->attachments = array_map(
            fn($a) => $a instanceof Attachment ? $a : new Attachment($a),
            $this->attachments ?? [],
        );
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
