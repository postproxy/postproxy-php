<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;

$client = new Client(
    apiKey: getenv('POSTPROXY_API_KEY'),
    profileGroupId: getenv('POSTPROXY_PROFILE_GROUP_ID') ?: null,
);

$postId = 'your-post-id';
$profileId = 'your-profile-id';

// List comments on a post
$comments = $client->comments()->list($postId, $profileId);
echo "Total comments: {$comments->total}\n";
foreach ($comments->data as $comment) {
    echo "  {$comment->authorUsername}: {$comment->body}\n";
    // Media attachments on the comment (image/video/gif/etc.)
    foreach ($comment->attachments as $att) {
        echo "    attachment: {$att->type} -> {$att->url}\n";
    }
    // Author signals exposed via metadata (verification, follower count, ...)
    if ($comment->metadata !== null) {
        echo "    metadata: " . json_encode($comment->metadata) . "\n";
    }
    foreach ($comment->replies as $reply) {
        echo "    {$reply->authorUsername}: {$reply->body}\n";
    }
}

// Filter the per-post list by when PostProxy received the comment
$recent = $client->comments()->list($postId, $profileId, from: '2026-03-25', to: '2026-03-26');
echo "Comments received 2026-03-25..26: {$recent->total}\n";

// List comments across every post in the profile group. Flat: replies come
// back as their own entries linked by parentExternalId.
$across = $client->comments()->listAll(profiles: ['instagram'], from: '2026-03-25', perPage: 50);
echo "Comments across posts: {$across->total}\n";
foreach ($across->data as $c) {
    $kind = $c->parentExternalId !== null ? 'reply' : 'comment';
    echo "  [{$c->platform}] {$kind} on post {$c->postId} — {$c->authorUsername}: {$c->body}\n";
}

// Create a comment. An idempotency key makes the write safe to retry after a
// dropped connection — the retry replays the original response instead of
// posting a second comment.
$newComment = $client->comments()->create(
    $postId,
    $profileId,
    'Thanks for the feedback!',
    idempotencyKey: bin2hex(random_bytes(16)),
);
echo "Created: {$newComment->id} (status: {$newComment->status})\n";

// Reply to a comment
$reply = $client->comments()->create($postId, $profileId, 'Glad you liked it!', parentId: $newComment->id);
echo "Reply: {$reply->id}\n";

// Hide / unhide
$client->comments()->hide($postId, $newComment->id, $profileId);
echo "Comment hidden\n";

$client->comments()->unhide($postId, $newComment->id, $profileId);
echo "Comment unhidden\n";

// Like / unlike
$client->comments()->like($postId, $newComment->id, $profileId);
echo "Comment liked\n";

$client->comments()->unlike($postId, $newComment->id, $profileId);
echo "Comment unliked\n";

// Private reply (Instagram/Facebook) — sends a DM in response to a comment.
// Returns a Message, not a Comment.
$privateReply = $client->comments()->privateReply($postId, $newComment->id, $profileId, 'DM-ing you the details!');
echo "Private reply queued: {$privateReply->id} (chat: {$privateReply->chatId})\n";

// Delete
$client->comments()->delete($postId, $newComment->id, $profileId);
echo "Comment deleted\n";
