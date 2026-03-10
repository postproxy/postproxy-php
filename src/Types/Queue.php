<?php

namespace PostProxy\Types;

class Queue extends Model
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $timezone = null;
    public ?bool $enabled = null;
    public ?int $jitter = null;
    public ?string $profileGroupId = null;
    public array $timeslots = [];
    public ?int $postsCount = null;

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->timeslots = array_map(function ($t) {
            if ($t instanceof Timeslot) {
                return $t;
            }
            return new Timeslot($t);
        }, $this->timeslots ?? []);
    }
}
