<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Message;
use PostProxy\Types\Model;

class ReactionEventData extends Model
{
    /** @var Message|null */
    public mixed $message = null;
    public ?string $senderExternalId = null;
    public ?string $action = null;
    public ?string $reaction = null;
    public ?string $emoji = null;
    public ?string $occurredAt = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->message)) {
            $this->message = new Message($this->message);
        }
    }
}
