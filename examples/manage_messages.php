<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;

$client = new Client(
    apiKey: getenv('POSTPROXY_API_KEY'),
    profileGroupId: getenv('POSTPROXY_PROFILE_GROUP_ID') ?: null,
);

// A DM-capable profile (facebook/instagram/telegram/bluesky)
$profileId = 'your-profile-id';

// List existing chats for a profile
$chats = $client->chats()->list($profileId, perPage: 20);
echo "Total chats: {$chats->total}\n";
foreach ($chats->data as $chat) {
    $who = $chat->participantUsername ?? $chat->participantExternalId;
    echo "  {$who}: last message at " . ($chat->lastMessageAt?->format('c') ?? 'never') . "\n";
}

// Find or create a chat with a participant
$chat = $client->chats()->create(
    $profileId,
    'igsid_8675309',
    participantUsername: 'jane_doe',
);
echo "Chat: {$chat->id} (platform: {$chat->platform})\n";

// List messages in the chat
$messages = $client->messages()->list($chat->id, direction: 'inbound');
foreach ($messages->data as $msg) {
    echo "  [{$msg->direction}] {$msg->body}\n";
    foreach ($msg->attachments as $att) {
        echo "    attachment: {$att->type} -> {$att->url}\n";
    }
}

// Send a text message (within the 24h window)
$sent = $client->messages()->send($chat->id, body: 'Yes, we ship worldwide!');
echo "Sent message: {$sent->id} (status: {$sent->status})\n";

// Send outside the 24h window with a tag (Facebook/Instagram)
$client->messages()->send($chat->id, body: 'Following up on your order.', tag: 'HUMAN_AGENT');

// Send an image by hosted URL
$client->messages()->send($chat->id, media: ['https://cdn.example.com/photo.png']);

// Send an image from a local file (multipart)
// $client->messages()->send($chat->id, mediaFiles: ['./photo.png']);

// React / unreact (Facebook & Instagram)
$client->messages()->react($sent->id, reaction: 'love', emoji: '❤️');
$client->messages()->unreact($sent->id);

// Edit an outbound message (Telegram only)
// $client->messages()->edit($sent->id, body: 'Updated answer.');

// Archive / unarchive a chat (Bluesky only)
// $client->chats()->archive($chat->id);
// $client->chats()->unarchive($chat->id);

// Private reply to a comment (Instagram/Facebook) — returns a Message
$reply = $client->comments()->privateReply(
    'your-post-id',
    'comment-id',
    $profileId,
    'Thanks — DM-ing you the details.',
);
echo "Private reply queued: {$reply->id} (chat: {$reply->chatId})\n";

// Ice breakers (Instagram only): FAQ prompts shown when a user opens a chat
$client->profiles()->setIceBreakers($profileId, [
    ['question' => 'What services do you offer?', 'payload' => 'services'],
    ['question' => 'What are your hours?', 'payload' => 'hours'],
]);
$result = $client->profiles()->iceBreakers($profileId);
foreach ($result->iceBreakers as $ib) {
    echo "Ice breaker: {$ib->question}\n";
}
// $client->profiles()->deleteIceBreakers($profileId);
