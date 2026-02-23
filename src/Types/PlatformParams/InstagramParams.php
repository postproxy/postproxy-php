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
}
