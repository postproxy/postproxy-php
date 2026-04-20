<?php

namespace PostProxy\Types;

class DeleteOnPlatformResponse extends Model
{
    public ?bool $success = null;
    public array $deleting = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->deleting = array_map(function ($d) {
            if ($d instanceof DeletingPlatform) {
                return $d;
            }
            return new DeletingPlatform($d);
        }, $this->deleting ?? []);
    }
}
