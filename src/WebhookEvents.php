<?php

namespace PostProxy;

use PostProxy\Exceptions\WebhookParseException;
use PostProxy\Types\WebhookEvents\CommentCreatedData;
use PostProxy\Types\WebhookEvents\Event;
use PostProxy\Types\WebhookEvents\MediaFailedData;
use PostProxy\Types\WebhookEvents\MessageEventData;
use PostProxy\Types\WebhookEvents\PlatformPostData;
use PostProxy\Types\WebhookEvents\PostImportedData;
use PostProxy\Types\WebhookEvents\PostProcessedData;
use PostProxy\Types\WebhookEvents\ProfileCommentCreatedData;
use PostProxy\Types\WebhookEvents\ProfileEventData;
use PostProxy\Types\WebhookEvents\ProfileStatsData;
use PostProxy\Types\WebhookEvents\ReactionEventData;

class WebhookEvents
{
    private const DATA_CLASSES = [
        'post.processed' => PostProcessedData::class,
        'post.imported' => PostImportedData::class,
        'platform_post.published' => PlatformPostData::class,
        'platform_post.failed' => PlatformPostData::class,
        'platform_post.failed_waiting_for_retry' => PlatformPostData::class,
        'platform_post.insights' => PlatformPostData::class,
        'profile.connected' => ProfileEventData::class,
        'profile.disconnected' => ProfileEventData::class,
        'profile.stats' => ProfileStatsData::class,
        'media.failed' => MediaFailedData::class,
        'comment.created' => CommentCreatedData::class,
        'profile_comment.created' => ProfileCommentCreatedData::class,
        'message.received' => MessageEventData::class,
        'message.sent' => MessageEventData::class,
        'message.delivered' => MessageEventData::class,
        'message.read' => MessageEventData::class,
        'message.edited' => MessageEventData::class,
        'message.deleted' => MessageEventData::class,
        'message.failed_waiting_for_retry' => MessageEventData::class,
        'message.failed' => MessageEventData::class,
        'reaction.received' => ReactionEventData::class,
    ];

    /**
     * Parse a webhook body and return a typed Event. Accepts a raw JSON string
     * or a pre-parsed associative array.
     */
    public static function parse(string|array $body): Event
    {
        if (is_string($body)) {
            $parsed = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WebhookParseException('Invalid JSON: ' . json_last_error_msg());
            }
            if (!is_array($parsed)) {
                throw new WebhookParseException('Webhook body is not a JSON object');
            }
        } else {
            $parsed = $body;
        }

        $type = $parsed['type'] ?? null;
        if (!is_string($type) || !isset(self::DATA_CLASSES[$type])) {
            throw new WebhookParseException('Unknown webhook event type: ' . var_export($type, true));
        }

        $dataClass = self::DATA_CLASSES[$type];
        $dataPayload = $parsed['data'] ?? [];
        if (!is_array($dataPayload)) {
            throw new WebhookParseException('Webhook event `data` must be an object');
        }

        return new Event(
            id: (string) ($parsed['id'] ?? ''),
            type: $type,
            createdAt: (string) ($parsed['created_at'] ?? ''),
            data: new $dataClass($dataPayload),
        );
    }
}
