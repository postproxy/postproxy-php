<?php

namespace PostProxy\Exceptions;

class PostProxyException extends \Exception
{
    public function __construct(
        string $message = '',
        public readonly ?int $statusCode = null,
        public readonly ?array $response = null,
    ) {
        parent::__construct($message);
    }
}
