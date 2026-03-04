# PostProxy PHP SDK

PHP client for the [PostProxy API](https://postproxy.dev) — manage social media posts, profiles, and profile groups.

## Requirements

- PHP >= 8.1
- Composer

## Installation

```bash
composer require postproxy/postproxy-php
```

## Quick Start

```php
use PostProxy\Client;

$client = new Client(apiKey: 'your-api-key');

// List profiles
$profiles = $client->profiles()->list();

// Create a post
$post = $client->posts()->create(
    'Hello world!',
    profiles: ['prof-1'],
);
```

## Configuration

```php
$client = new Client(
    apiKey: 'your-api-key',
    profileGroupId: 'pg-123',  // Default profile group for all requests
);
```

## Resources

### Posts

```php
// List posts with filters
$result = $client->posts()->list(page: 1, perPage: 10, status: 'processed');
$result->data;    // Post[]
$result->total;   // int
$result->page;    // int
$result->perPage; // int

// Get a single post
$post = $client->posts()->get('post-id');

// Create a post
$post = $client->posts()->create(
    'Post body',
    profiles: ['prof-1', 'prof-2'],
    media: ['https://example.com/image.jpg'],
    scheduledAt: '2025-06-01T12:00:00Z',
    draft: true,
);

// Create a post with file uploads
$post = $client->posts()->create(
    'Post with uploads',
    profiles: ['prof-1'],
    mediaFiles: ['/path/to/image.jpg'],
);

// Create a thread post
$post = $client->posts()->create(
    'Thread starts here',
    profiles: ['prof-1'],
    thread: [
        ['body' => 'Second post in the thread'],
        ['body' => 'Third with media', 'media' => ['https://example.com/img.jpg']],
    ],
);
foreach ($post->thread as $child) {
    echo "{$child->id}: {$child->body}\n";
}

// Publish a draft
$post = $client->posts()->publishDraft('post-id');

// Delete a post
$result = $client->posts()->delete('post-id');

// Get stats for posts
$stats = $client->posts()->stats(['post-1', 'post-2']);
foreach ($stats->data as $postId => $postStats) {
    foreach ($postStats->platforms as $platform) {
        echo "{$platform->platform}: " . count($platform->records) . " snapshots\n";
        foreach ($platform->records as $record) {
            echo "  {$record->recordedAt->format('Y-m-d')}: " . json_encode($record->stats) . "\n";
        }
    }
}

// Filter stats by profiles/networks and time range
$stats = $client->posts()->stats(
    ['post-1'],
    profiles: ['instagram', 'twitter'],
    from: '2026-02-01T00:00:00Z',
    to: '2026-02-24T00:00:00Z',
);
```

### Webhooks

```php
// List webhooks
$webhooks = $client->webhooks()->list();

// Get a webhook
$webhook = $client->webhooks()->get('wh-id');

// Create a webhook
$webhook = $client->webhooks()->create(
    'https://example.com/webhook',
    events: ['post.published', 'post.failed'],
    description: 'My webhook',
);
echo $webhook->secret;

// Update a webhook
$webhook = $client->webhooks()->update('wh-id', events: ['post.published'], enabled: false);

// Delete a webhook
$client->webhooks()->delete('wh-id');

// List deliveries
$deliveries = $client->webhooks()->deliveries('wh-id', page: 1, perPage: 10);
foreach ($deliveries->data as $d) {
    echo "{$d->eventType}: {$d->success}\n";
}
```

#### Signature verification

Verify incoming webhook signatures using HMAC-SHA256:

```php
use PostProxy\WebhookSignature;

$isValid = WebhookSignature::verify(
    payload: $request->getContent(),
    signatureHeader: $request->headers->get('X-PostProxy-Signature'),
    secret: 'whsec_...',
);
```

### Profiles

```php
// List profiles
$result = $client->profiles()->list();

// Get a single profile
$profile = $client->profiles()->get('prof-id');

// Get placements for a profile
$placements = $client->profiles()->placements('prof-id');

// Delete a profile
$result = $client->profiles()->delete('prof-id');
```

### Profile Groups

```php
// List profile groups
$result = $client->profileGroups()->list();

// Get a single profile group
$group = $client->profileGroups()->get('pg-id');

// Create a profile group
$group = $client->profileGroups()->create('My Group');

// Delete a profile group
$result = $client->profileGroups()->delete('pg-id');

// Initialize an OAuth connection
$connection = $client->profileGroups()->initializeConnection(
    'pg-id',
    platform: 'instagram',
    redirectUrl: 'https://myapp.com/callback',
);
echo $connection->url; // Redirect user here
```

## Platform Parameters

```php
use PostProxy\Types\PlatformParams\PlatformParams;
use PostProxy\Types\PlatformParams\FacebookParams;
use PostProxy\Types\PlatformParams\InstagramParams;

$platforms = new PlatformParams([
    'facebook' => new FacebookParams(['format' => 'post', 'first_comment' => 'Hi!']),
    'instagram' => new InstagramParams(['format' => 'reel']),
]);

$post = $client->posts()->create('Hello!', profiles: ['prof-1'], platforms: $platforms);
```

## Error Handling

```php
use PostProxy\Exceptions\AuthenticationException;
use PostProxy\Exceptions\NotFoundException;
use PostProxy\Exceptions\ValidationException;
use PostProxy\Exceptions\BadRequestException;
use PostProxy\Exceptions\PostProxyException;

try {
    $client->posts()->get('bad-id');
} catch (AuthenticationException $e) {
    // 401
} catch (NotFoundException $e) {
    // 404
} catch (ValidationException $e) {
    // 422
} catch (BadRequestException $e) {
    // 400
} catch (PostProxyException $e) {
    // Other errors
    echo $e->getMessage();
    echo $e->statusCode;
    echo print_r($e->response, true);
}
```

## Development

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT
