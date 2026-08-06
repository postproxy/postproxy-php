<?php

namespace PostProxy\Types\PlatformParams;

/**
 * An Instagram account to tag in a post.
 *
 * Images require `x` and `y` (floats 0.0-1.0 from the top-left corner); reels
 * and video slides are tagged by username only — Instagram ignores coordinates
 * there. `mediaIndex` picks the carousel slide (0-based, defaults to 0).
 */
class InstagramUserTag
{
    public function __construct(
        public readonly string $username,
        public readonly ?float $x = null,
        public readonly ?float $y = null,
        public readonly ?int $mediaIndex = null,
    ) {}

    public function toArray(): array
    {
        $result = ['username' => $this->username];
        if ($this->x !== null) $result['x'] = $this->x;
        if ($this->y !== null) $result['y'] = $this->y;
        if ($this->mediaIndex !== null) $result['media_index'] = $this->mediaIndex;
        return $result;
    }
}
