<?php

namespace PostProxy\Exceptions;

/**
 * 409. Thrown for a duplicate submission (`$e->response['duplicate_post_id']`),
 * a backfill that is already running (`$e->response['profile_sync_id']`), or a
 * request whose Idempotency-Key is still in flight.
 */
class ConflictException extends PostProxyException {}
