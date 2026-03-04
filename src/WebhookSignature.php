<?php

namespace PostProxy;

class WebhookSignature
{
    public static function verify(string $payload, string $signatureHeader, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        $expected = $parts['v1'] ?? '';

        if ($timestamp === '' || $expected === '') {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $computed = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($computed, $expected);
    }
}
