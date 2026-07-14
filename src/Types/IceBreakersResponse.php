<?php

namespace PostProxy\Types;

class IceBreakersResponse extends Model
{
    /** @var IceBreaker[] */
    public array $iceBreakers = [];

    public function __construct(array $attrs = [])
    {
        $items = $attrs['ice_breakers'] ?? [];
        unset($attrs['ice_breakers']);
        parent::__construct($attrs);
        $this->iceBreakers = array_map(
            fn($ib) => $ib instanceof IceBreaker ? $ib : new IceBreaker($ib),
            $items,
        );
    }
}
