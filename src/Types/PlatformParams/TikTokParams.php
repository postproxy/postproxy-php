<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class TikTokParams extends Model
{
    public ?string $format = null;
    public ?string $privacyStatus = null;
    public ?int $photoCoverIndex = null;
    public ?bool $autoAddMusic = null;
    public ?bool $madeWithAi = null;
    public ?bool $disableComment = null;
    public ?bool $disableDuet = null;
    public ?bool $disableStitch = null;
    public ?bool $brandContentToggle = null;
    public ?bool $brandOrganicToggle = null;
}
