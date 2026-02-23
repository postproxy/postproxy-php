<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class PinterestParams extends Model
{
    public ?string $format = null;
    public ?string $title = null;
    public ?string $boardId = null;
    public ?string $destinationLink = null;
    public ?string $coverUrl = null;
    public ?int $thumbOffset = null;
}
