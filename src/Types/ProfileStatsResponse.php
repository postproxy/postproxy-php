<?php

namespace PostProxy\Types;

class ProfileStatsResponse
{
    public ProfileStats $data;

    public function __construct(array $attrs = [])
    {
        $payload = $attrs['data'] ?? [];
        $this->data = $payload instanceof ProfileStats ? $payload : new ProfileStats($payload);
    }
}
