<?php

namespace PostProxy\Types;

/**
 * A button attached to the message itself, delivered as a Meta generic template.
 *
 * Facebook and Instagram only; up to 3 per send. `url` is required and must be
 * https when `type` is "web_url"; `payload` is required when `type` is
 * "postback". `type` is a plain string rather than an enum so a new Meta button
 * type needs no SDK release.
 */
class MessageButton extends Model
{
    public ?string $type = null;
    public ?string $title = null;
    public ?string $url = null;
    public ?string $payload = null;

    /** A button that opens a link. The URL must be https. */
    public static function webUrl(string $title, string $url): self
    {
        return new self(['type' => 'web_url', 'title' => $title, 'url' => $url]);
    }

    /** A button that posts your payload back as an inbound message. */
    public static function postback(string $title, string $payload): self
    {
        return new self(['type' => 'postback', 'title' => $title, 'payload' => $payload]);
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'payload' => $this->payload,
        ], fn ($v) => $v !== null);
    }
}
