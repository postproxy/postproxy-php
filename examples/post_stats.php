<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;

$client = new Client(apiKey: 'your-api-key', profileGroupId: 'pg-123');

// Get stats for multiple posts
$stats = $client->posts()->stats(['post-1', 'post-2']);

foreach ($stats->data as $postId => $postStats) {
    echo "Post: {$postId}\n";

    foreach ($postStats->platforms as $platform) {
        echo "  {$platform->platform} (profile: {$platform->profileId})\n";

        foreach ($platform->records as $record) {
            echo "    Recorded at: {$record->recordedAt->format('Y-m-d H:i:s')}\n";
            foreach ($record->stats as $metric => $value) {
                echo "      {$metric}: {$value}\n";
            }
        }
    }
}

// Filter by profiles/networks and time range
$stats = $client->posts()->stats(
    ['post-1'],
    profiles: ['instagram', 'twitter'],
    from: '2026-02-01T00:00:00Z',
    to: '2026-02-24T00:00:00Z',
);

// Using DateTimeImmutable for time range
$stats = $client->posts()->stats(
    ['post-1'],
    from: new DateTimeImmutable('2026-02-01'),
    to: new DateTimeImmutable('2026-02-24'),
);
