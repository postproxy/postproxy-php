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
    /** @var QuickReply[]|null */
    public ?array $quickReplies = null;
    /** @var MessageButton[]|null */
    public ?array $buttons = null;
    public mixed $card = null;
    public mixed $tappedAction = null;
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
        // Left null rather than [] when absent — the API omits these on non-Meta
        // networks, and an empty array would read as "sent with none".
        if ($this->quickReplies !== null) {
            $this->quickReplies = array_map(function ($q) {
                return $q instanceof QuickReply ? $q : new QuickReply($q);
            }, $this->quickReplies);
        }
        if ($this->buttons !== null) {
            $this->buttons = array_map(function ($b) {
                return $b instanceof MessageButton ? $b : new MessageButton($b);
            }, $this->buttons);
        }
        if ($this->card !== null && !$this->card instanceof MessageCard) {
            $this->card = new MessageCard($this->card);
        }
        if ($this->tappedAction !== null && !$this->tappedAction instanceof TappedAction) {
            $this->tappedAction = new TappedAction($this->tappedAction);
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
