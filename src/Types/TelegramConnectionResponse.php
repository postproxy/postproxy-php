<?php

namespace PostProxy\Types;

class TelegramConnectionResponse extends Model
{
    public ?bool $success = null;
    public mixed $profile = null;
    public ?string $nextStep = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->profile)) {
            $this->profile = new SyncProfile($this->profile);
        }
    }
}
