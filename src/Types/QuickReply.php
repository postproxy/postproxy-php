<?php

namespace PostProxy\Types;

/**
 * A tappable chip rendered above the participant's composer, gone once tapped.
 *
 * Facebook and Instagram only; up to 13 per send. `contentType` is optional on
 * send (only "text" is accepted) and always present on responses. `title` is
 * capped at 20 characters and `payload` at 1000; both are required.
 */
class QuickReply extends Model
{
    public ?string $contentType = null;
    public ?string $title = null;
    public ?string $payload = null;

    public static function make(string $title, string $payload): self
    {
        return new self(['title' => $title, 'payload' => $payload]);
    }

    /** Nulls are dropped so an omitted content_type stays omitted on the wire. */
    public function toArray(): array
    {
        return array_filter([
            'content_type' => $this->contentType,
            'title' => $this->title,
            'payload' => $this->payload,
        ], fn ($v) => $v !== null);
    }
}
