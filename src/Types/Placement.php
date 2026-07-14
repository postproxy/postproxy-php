<?php

namespace PostProxy\Types;

class Placement extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public ?array $metadata = null;
    /** Set in the response of Profiles::assignPlacementToGroup(). */
    public ?string $profileGroupId = null;
}
