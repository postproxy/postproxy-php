<?php

namespace PostProxy\Types;

class StatsResponse
{
    /** @var array<string, PostStats> */
    public readonly array $data;

    /**
     * @param array<string, PostStats> $data Keyed by post ID
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
