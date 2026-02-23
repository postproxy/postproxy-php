<?php

namespace PostProxy\Types;

class ListResponse
{
    public function __construct(
        public readonly array $data,
    ) {}
}
