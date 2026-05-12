<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class TelegramParams extends Model
{
    public ?string $format = null;
    public ?string $chatId = null;
    public ?string $parseMode = null;
    public ?bool $disableLinkPreview = null;
    public ?bool $disableNotification = null;
}
