<?php

namespace PostProxy\Types;

/**
 * Set on inbound messages created by a tap on an interactive element you sent.
 *
 * Derived from `platformData` rather than stored, so it also resolves for taps
 * ingested before PostProxy exposed this field. `kind` is one of "quick_reply",
 * "postback", or "callback_query" — the last is Telegram, so this is not
 * Meta-only even though the send params are.
 */
class TappedAction extends Model
{
    public const KIND_QUICK_REPLY = 'quick_reply';
    public const KIND_POSTBACK = 'postback';
    public const KIND_CALLBACK_QUERY = 'callback_query';

    public ?string $kind = null;
    /** The payload you set on the quick reply, button, or ice breaker. */
    public ?string $payload = null;
    /** The label the participant tapped. */
    public ?string $title = null;
}
