<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class InstagramParams extends Model
{
    public ?string $format = null;
    public ?string $firstComment = null;
    public ?array $collaborators = null;
    public ?string $coverUrl = null;
    public ?string $audioName = null;
    public ?string $trialStrategy = null;
    public ?int $thumbOffset = null;
    /** @var InstagramUserTag[]|array[]|null */
    public ?array $userTags = null;

    /**
     * User tags must reach the wire as plain arrays, with unset coordinates
     * dropped rather than sent as nulls.
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        if ($this->userTags !== null) {
            $result['user_tags'] = array_map(
                fn($tag) => $tag instanceof InstagramUserTag
                    ? $tag->toArray()
                    : array_filter($tag, fn($v) => $v !== null),
                $this->userTags,
            );
        }

        return $result;
    }
}
