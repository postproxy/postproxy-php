<?php

namespace PostProxy\Types;

class PostStats
{
    /** @var PlatformStats[] */
    public array $platforms;

    public function __construct(array $data)
    {
        $this->platforms = array_map(
            fn($p) => $p instanceof PlatformStats ? $p : new PlatformStats($p),
            $data['platforms'] ?? [],
        );
    }
}
