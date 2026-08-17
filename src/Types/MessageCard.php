<?php

namespace PostProxy\Types;

/**
 * Extra fields for the generic-template element that carries `buttons`, for a
 * richer product-style card.
 *
 * Requires `buttons`. `subtitle` is capped at 80 characters, and both
 * `imageUrl` and the default action's URL must be https.
 */
class MessageCard extends Model
{
    public ?string $subtitle = null;
    public ?string $imageUrl = null;
    public mixed $defaultAction = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if ($this->defaultAction !== null && !$this->defaultAction instanceof CardDefaultAction) {
            $this->defaultAction = new CardDefaultAction($this->defaultAction);
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'subtitle' => $this->subtitle,
            'image_url' => $this->imageUrl,
            'default_action' => $this->defaultAction?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
