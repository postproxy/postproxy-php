<?php

namespace PostProxy\Resources;

use PostProxy\Client;
use PostProxy\Types\Message;
use PostProxy\Types\MessageButton;
use PostProxy\Types\MessageCard;
use PostProxy\Types\Model;
use PostProxy\Types\PaginatedResponse;
use PostProxy\Types\QuickReply;

class Messages
{
    public function __construct(private readonly Client $client) {}

    public function list(
        string $chatId,
        ?int $page = null,
        ?int $perPage = null,
        ?string $direction = null,
        ?string $status = null,
        ?string $profileGroupId = null,
    ): PaginatedResponse {
        $params = [];
        if ($page !== null) $params['page'] = $page;
        if ($perPage !== null) $params['per_page'] = $perPage;
        if ($direction !== null) $params['direction'] = $direction;
        if ($status !== null) $params['status'] = $status;

        $result = $this->client->request('GET', "/chats/{$chatId}/messages", params: $params, profileGroupId: $profileGroupId);
        $messages = array_map(fn($m) => new Message($m), $result['data'] ?? []);
        return new PaginatedResponse(
            data: $messages,
            total: $result['total'],
            page: $result['page'],
            perPage: $result['per_page'],
        );
    }

    /**
     * Send a message to a chat.
     *
     * $quickReplies, $buttons, and $card are Facebook and Instagram only — they
     * return 422 on Telegram and Bluesky, where $replyMarkup is the equivalent.
     * They are sent on the JSON path only, so pass $media as hosted URLs rather
     * than $mediaFiles when combining with an attachment.
     *
     * Each accepts model instances or plain arrays.
     *
     * @param QuickReply[]|array[]|null    $quickReplies Up to 13.
     * @param MessageButton[]|array[]|null $buttons      Up to 3.
     * @param MessageCard|array|null       $card         Requires $buttons.
     */
    public function send(
        string $chatId,
        ?string $body = null,
        ?array $media = null,
        ?array $mediaFiles = null,
        ?string $tag = null,
        ?string $replyToExternalId = null,
        ?array $replyMarkup = null,
        ?array $quickReplies = null,
        ?array $buttons = null,
        MessageCard|array|null $card = null,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): Message {
        $hasFiles = $mediaFiles !== null && count($mediaFiles) > 0;

        if ($hasFiles) {
            $formData = [];
            if ($body !== null) $formData['body'] = $body;
            if ($tag !== null) $formData['tag'] = $tag;
            if ($replyToExternalId !== null) $formData['reply_to_external_id'] = $replyToExternalId;

            $files = [];

            if ($media !== null) {
                foreach ($media as $m) {
                    $files[] = ['media[]', null, $m, 'text/plain'];
                }
            }

            foreach ($mediaFiles as $path) {
                $path = (string) $path;
                $filename = basename($path);
                $contentType = $this->mimeTypeFor($filename);
                $io = fopen($path, 'rb');
                $files[] = ['media[]', $filename, $io, $contentType];
            }

            $result = $this->client->request('POST', "/chats/{$chatId}/messages",
                data: $formData,
                files: $files,
                profileGroupId: $profileGroupId,
                idempotencyKey: $idempotencyKey,
            );
        } else {
            $jsonBody = [];
            if ($body !== null) $jsonBody['body'] = $body;
            if ($media !== null) $jsonBody['media'] = $media;
            if ($tag !== null) $jsonBody['tag'] = $tag;
            if ($replyToExternalId !== null) $jsonBody['reply_to_external_id'] = $replyToExternalId;
            if ($replyMarkup !== null) $jsonBody['reply_markup'] = $replyMarkup;
            if ($quickReplies !== null) {
                $jsonBody['quick_replies'] = array_map([self::class, 'serializeInteractive'], $quickReplies);
            }
            if ($buttons !== null) {
                $jsonBody['buttons'] = array_map([self::class, 'serializeInteractive'], $buttons);
            }
            if ($card !== null) $jsonBody['card'] = self::serializeInteractive($card);

            $result = $this->client->request('POST', "/chats/{$chatId}/messages", json: $jsonBody, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        }

        return new Message($result);
    }

    public function get(string $messageId, ?string $profileGroupId = null): Message
    {
        $result = $this->client->request('GET', "/messages/{$messageId}", profileGroupId: $profileGroupId);
        return new Message($result);
    }

    public function edit(
        string $messageId,
        ?string $body = null,
        ?array $replyMarkup = null,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): Message {
        $jsonBody = [];
        if ($body !== null) $jsonBody['body'] = $body;
        if ($replyMarkup !== null) $jsonBody['reply_markup'] = $replyMarkup;

        $result = $this->client->request('PATCH', "/messages/{$messageId}", json: $jsonBody, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new Message($result);
    }

    public function react(
        string $messageId,
        ?string $reaction = null,
        ?string $emoji = null,
        ?string $profileGroupId = null,
        ?string $idempotencyKey = null,
    ): Message {
        $jsonBody = [];
        if ($reaction !== null) $jsonBody['reaction'] = $reaction;
        if ($emoji !== null) $jsonBody['emoji'] = $emoji;

        $result = $this->client->request('POST', "/messages/{$messageId}/react", json: $jsonBody ?: null, profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new Message($result);
    }

    public function unreact(string $messageId, ?string $profileGroupId = null, ?string $idempotencyKey = null): Message
    {
        $result = $this->client->request('DELETE', "/messages/{$messageId}/unreact", profileGroupId: $profileGroupId, idempotencyKey: $idempotencyKey);
        return new Message($result);
    }

    /**
     * Interactive params accept model instances or plain arrays. The models'
     * toArray() drops nulls, so an omitted content_type stays omitted rather
     * than being sent as null.
     */
    private static function serializeInteractive(mixed $value): array
    {
        return $value instanceof Model ? $value->toArray() : $value;
    }

    private function mimeTypeFor(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => 'application/octet-stream',
        };
    }
}
