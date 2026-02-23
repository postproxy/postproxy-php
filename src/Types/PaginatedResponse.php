<?php

namespace PostProxy\Types;

class PaginatedResponse extends ListResponse
{
    public function __construct(
        array $data,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
    ) {
        parent::__construct($data);
    }
}
