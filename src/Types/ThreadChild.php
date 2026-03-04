<?php

namespace PostProxy\Types;

class ThreadChild extends Model
{
    public ?string $id = null;
    public ?string $body = null;
    public array $media = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->media = array_map(function ($m) {
            if ($m instanceof Media) {
                return $m;
            }
            return new Media($m);
        }, $this->media ?? []);
    }
}
