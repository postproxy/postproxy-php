<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class YouTubeParams extends Model
{
    public ?string $format = null;
    public ?string $title = null;
    public ?string $privacyStatus = null;
    public ?string $coverUrl = null;
    public ?bool $madeForKids = null;
    public ?array $tags = null;
    public ?string $categoryId = null;
    public ?bool $containsSyntheticMedia = null;
}
