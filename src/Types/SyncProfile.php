<?php

namespace PostProxy\Types;

class SyncProfile extends Model
{
    public ?string $id = null;
    public ?string $network = null;
    public ?string $name = null;
    public ?string $externalUsername = null;
}
