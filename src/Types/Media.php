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
}
