<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class FacebookParams extends Model
{
    public ?string $format = null;
    public ?string $title = null;
    public ?string $firstComment = null;
    public ?string $pageId = null;
}
