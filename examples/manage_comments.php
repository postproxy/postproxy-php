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

// Create a comment
$newComment = $client->comments()->create($postId, $profileId, 'Thanks for the feedback!');
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
