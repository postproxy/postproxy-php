<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;

$apiKey = getenv('POSTPROXY_API_KEY');
$profileGroupId = getenv('POSTPROXY_PROFILE_GROUP_ID');

$client = new Client(apiKey: $apiKey, profileGroupId: $profileGroupId);

// Create a queue with weekday morning timeslots
$queue = $client->queues()->create(
    'Morning Posts',
    $profileGroupId,
    description: 'Weekday morning content',
    timezone: 'America/New_York',
    jitter: 10,
    timeslots: [
        ['day' => 1, 'time' => '09:00'],
        ['day' => 2, 'time' => '09:00'],
        ['day' => 3, 'time' => '09:00'],
        ['day' => 4, 'time' => '09:00'],
        ['day' => 5, 'time' => '09:00'],
    ],
);
echo "Created queue: {$queue->id} {$queue->name}\n";
echo "Timeslots: " . count($queue->timeslots) . "\n";

// List all queues
$queues = $client->queues()->list();
foreach ($queues->data as $q) {
    echo "Queue: {$q->name}\n";
}

// Get next available slot
$nextSlot = $client->queues()->nextSlot($queue->id);
echo "Next slot: {$nextSlot->nextSlot}\n";

// Add a post to the queue
$profiles = $client->profiles()->list();
$post = $client->posts()->create(
    'This post will be scheduled by the queue',
    profiles: [$profiles->data[0]->id],
    queueId: $queue->id,
    queuePriority: 'high',
);
echo "Queued post: {$post->id} scheduled at: {$post->scheduledAt?->format('c')}\n";

// Update the queue — add a timeslot and change jitter
$updated = $client->queues()->update($queue->id, jitter: 15, timeslots: [['day' => 6, 'time' => '10:00']]);
echo "Updated queue timeslots: " . count($updated->timeslots) . "\n";

// Pause the queue
$paused = $client->queues()->update($queue->id, enabled: false);
echo "Queue paused: " . (!$paused->enabled ? 'true' : 'false') . "\n";

// Delete the queue
$deleted = $client->queues()->delete($queue->id);
echo "Deleted: " . ($deleted->deleted ? 'true' : 'false') . "\n";
