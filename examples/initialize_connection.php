<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostProxy\Client;

$client = new Client(apiKey: 'your-api-key');

// Create a profile group
$group = $client->profileGroups()->create('My App');
echo "Created group: {$group->id}\n";

// Initialize an OAuth connection for Instagram
$connection = $client->profileGroups()->initializeConnection(
    $group->id,
    platform: 'instagram',
    redirectUrl: 'https://myapp.com/callback',
);

echo "Redirect user to: {$connection->url}\n";
