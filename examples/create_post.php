<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;
use PostProxy\Types\PlatformParams\FacebookParams;
use PostProxy\Types\PlatformParams\InstagramParams;
use PostProxy\Types\PlatformParams\PlatformParams;

$client = new Client(apiKey: 'your-api-key', profileGroupId: 'pg-123');

// List profiles
$profiles = $client->profiles()->list();
foreach ($profiles->data as $profile) {
    echo "{$profile->id}: {$profile->name} ({$profile->platform})\n";
}

// Create a post with platform-specific parameters
$platforms = new PlatformParams([
    'facebook' => new FacebookParams(['format' => 'post', 'first_comment' => 'Thanks for reading!']),
    'instagram' => new InstagramParams(['format' => 'post']),
]);

$post = $client->posts()->create(
    'Hello from PostProxy PHP SDK!',
    profiles: ['instagram', 'facebook'],
    media: ['https://example.com/image.jpg'],
    platforms: $platforms,
    draft: true,
);

echo "Created post: {$post->id} (status: {$post->status})\n";

// List posts with filters
$result = $client->posts()->list(page: 1, perPage: 10, status: 'processed');
echo "Total posts: {$result->total}\n";
foreach ($result->data as $p) {
    echo "  {$p->id}: {$p->body}\n";
}

// Create a thread post
$threadPost = $client->posts()->create(
    "Here's a thread about PostProxy 🧵",
    profiles: ['twitter-profile-id'],
    thread: [
        ['body' => 'First, connect your social accounts.'],
        ['body' => 'Then, create posts with media!', 'media' => ['https://example.com/demo.jpg']],
        ['body' => 'Finally, schedule or publish instantly.'],
    ],
);
echo "Thread post: {$threadPost->id} (" . count($threadPost->thread) . " children)\n";
