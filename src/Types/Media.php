<?php

namespace PostProxy\Types;

class Media extends Model
{
    public ?string $id = null;
    public ?string $status = null;
    public ?string $errorMessage = null;
    public ?string $contentType = null;
    public ?string $sourceUrl = null;
    public ?string $url = null;
    /** @var MediaPlatformError[]|null */
    public ?array $platforms = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->platforms)) {
            $this->platforms = array_map(
                fn ($p) => $p instanceof MediaPlatformError ? $p : new MediaPlatformError($p),
                $this->platforms,
            );
        }
    }
}
