<?php

// Backfill a profile's older posts and follow the sync run to completion.

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;
use PostProxy\Exceptions\ConflictException;

$client = new Client(
    apiKey: getenv('POSTPROXY_API_KEY'),
    profileGroupId: getenv('POSTPROXY_PROFILE_GROUP_ID') ?: null,
);

$profileId = 'your-profile-id';

// Start a backfill. It walks the profile's feed backwards from the newest post
// in batches of 25 and stops at `from` — or earlier, if the platform stops
// returning history. Runs in the background.
try {
    $sync = $client->profiles()->backfillPosts($profileId, '2025-01-01');
} catch (ConflictException $e) {
    // Only one backfill runs per profile at a time; the running one already
    // covers any window a second request could ask for.
    $runningId = $e->response['profile_sync_id'];
    echo "Backfill already running: {$runningId}\n";
    $sync = $client->profiles()->postSync($profileId, $runningId);
}

echo "Backfill {$sync->id} — status: {$sync->status}\n";

// Poll until it finishes.
while (in_array($sync->status, ['pending', 'running'], true)) {
    sleep(5);
    $sync = $client->profiles()->postSync($profileId, $sync->id);
    $oldest = $sync->oldestPostedAt?->format(DATE_ATOM) ?? '—';
    echo "  {$sync->status}: {$sync->postsImported} imported of {$sync->postsSeen} seen, reached back to {$oldest}\n";
}

if ($sync->status === 'failed') {
    echo "Backfill failed: {$sync->error}\n";
} else {
    echo "Done. Imported {$sync->postsImported} posts\n";
}

// Every pull is recorded — the sync fired on connect, the recurring poll, and
// each backfill. Runs are kept for 30 days.
$runs = $client->profiles()->postSyncs($profileId, perPage: 10);
echo "\nRecent post syncs ({$runs->total}):\n";
foreach ($runs->data as $run) {
    $created = $run->createdAt?->format(DATE_ATOM) ?? '';
    echo "  {$created} {$run->trigger} → {$run->status} ({$run->postsImported}/{$run->postsSeen} new)\n";
}
