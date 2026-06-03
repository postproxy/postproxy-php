<?php

namespace PostProxy\Types;

class Attachment extends Model
{
    public ?string $id = null;
    public ?string $type = null;
    public ?string $url = null;
    public ?string $status = null;
    public ?string $externalId = null;
}
