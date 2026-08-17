<?php

namespace PostProxy\Types;

/** The tap-through on a MessageCard. The URL must be https. */
class CardDefaultAction extends Model
{
    public ?string $type = null;
    public ?string $url = null;

    public static function webUrl(string $url): self
    {
        return new self(['type' => 'web_url', 'url' => $url]);
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
