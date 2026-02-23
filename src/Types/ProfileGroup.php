<?php

namespace PostProxy\Types;

class ProfileGroup extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public int $profilesCount = 0;
}
