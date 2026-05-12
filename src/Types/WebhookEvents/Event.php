<?php

namespace PostProxy\Types\WebhookEvents;

class Event
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $createdAt,
        public readonly object $data,
    ) {}
}
