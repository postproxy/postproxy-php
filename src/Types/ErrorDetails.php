<?php

namespace PostProxy\Types;

class ErrorDetails extends Model
{
    public ?string $platformErrorCode = null;
    public ?string $platformErrorSubcode = null;
    public ?string $platformErrorMessage = null;
    public ?string $postproxyNote = null;
}
