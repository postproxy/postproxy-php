<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class YouTubeParams extends Model
{
    public ?string $format = null;
    public ?string $title = null;
    public ?string $privacyStatus = null;
    public ?string $coverUrl = null;
}
