<?php

namespace PostProxy\Types;

class MediaPlatformError extends Model
{
    public ?string $platform = null;
    public ?string $status = null;
    public ?string $error = null;
    public mixed $errorDetails = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        if (is_array($this->errorDetails)) {
            $this->errorDetails = new ErrorDetails($this->errorDetails);
        }
    }
}
