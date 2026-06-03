<?php

namespace PostProxy\Types;

class Message extends Model
{
    public ?string $id = null;
    public ?string $chatId = null;
    public ?string $externalId = null;
    public ?string $direction = null;
    public ?string $body = null;
    public ?string $status = null;
    public ?string $tag = null;
    public ?string $externalCommentId = null;
    public ?string $errorMessage = null;
    public mixed $platformData = null;
    public mixed $externalPostedAt = null;
    public mixed $externalDeliveredAt = null;
    public mixed $externalReadAt = null;
    public mixed $externalEditedAt = null;
    public ?string $replyToExternalId = null;
    public mixed $replyMarkup = null;
    public mixed $externalDeletedAt = null;
    /** @var Reaction[] */
    public array $reactions = [];
    /** @var Attachment[] */
    public array $attachments = [];
    public bool $isUnsupported = false;
    public mixed $createdAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->externalPostedAt = self::parseTime($this->externalPostedAt);
        $this->externalDeliveredAt = self::parseTime($this->externalDeliveredAt);
        $this->externalReadAt = self::parseTime($this->externalReadAt);
        $this->externalEditedAt = self::parseTime($this->externalEditedAt);
        $this->externalDeletedAt = self::parseTime($this->externalDeletedAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->reactions = array_map(function ($r) {
            return $r instanceof Reaction ? $r : new Reaction($r);
        }, $this->reactions ?? []);
        $this->attachments = array_map(function ($a) {
            return $a instanceof Attachment ? $a : new Attachment($a);
        }, $this->attachments ?? []);
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
