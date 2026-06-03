<?php

namespace PostProxy\Types\WebhookEvents;

use PostProxy\Types\Message;
use PostProxy\Types\Model;

class MessageEventData extends Model
{
    /** @var Message|null */
    public mixed $message = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->message)) {
            $this->message = new Message($this->message);
        }
    }
}
