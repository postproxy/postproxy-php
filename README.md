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

// Delete a post and also remove it from social platforms
$result = $client->posts()->delete('post-id', deleteOnPlatform: true);

// Delete from platforms only (keeps DB record). Defaults to all platforms.
$r1 = $client->posts()->deleteOnPlatform('post-id');
// Target a single network
$r2 = $client->posts()->deleteOnPlatform('post-id', network: 'twitter');
// Target a specific profile
$r3 = $client->posts()->deleteOnPlatform('post-id', profileId: 'prof-abc');
// Target a specific post profile (covers entire thread for that profile)
$r4 = $client->posts()->deleteOnPlatform('post-id', postProfileId: 'pp-abc');

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

### Queues

```php
// List all queues
$queues = $client->queues()->list();

// Get a queue
$queue = $client->queues()->get('queue-id');

// Get next available slot
$nextSlot = $client->queues()->nextSlot('queue-id');
echo $nextSlot->nextSlot;

// Create a queue with timeslots
$queue = $client->queues()->create(
    'Morning Posts',
    'profile-group-id',
    description: 'Weekday morning content',
    timezone: 'America/New_York',
    jitter: 10,
    timeslots: [
        ['day' => 1, 'time' => '09:00'],
        ['day' => 2, 'time' => '09:00'],
        ['day' => 3, 'time' => '09:00'],
    ],
);

// Update a queue
$queue = $client->queues()->update('queue-id',
    jitter: 15,
    timeslots: [
        ['day' => 6, 'time' => '10:00'],        // add new timeslot
        ['id' => 1, '_destroy' => true],          // remove existing timeslot
    ],
);

// Pause/unpause a queue
$client->queues()->update('queue-id', enabled: false);

// Delete a queue
$client->queues()->delete('queue-id');

// Add a post to a queue
$post = $client->posts()->create(
    'This post will be scheduled by the queue',
    profiles: ['prof-1'],
    queueId: 'queue-id',
    queuePriority: 'high',
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

#### Event types and typed payloads

Subscribe to any of these events (or pass `["*"]` for all):

`post.processed`, `post.imported`, `platform_post.published`, `platform_post.failed`, `platform_post.failed_waiting_for_retry`, `platform_post.insights`, `profile.connected`, `profile.disconnected`, `profile.stats`, `media.failed`, `comment.created`.

`WebhookEvents::parse` validates the envelope and returns a typed `Event` — `$event->data` is the right model for the event:

```php
use PostProxy\WebhookEvents;
use PostProxy\Types\WebhookEvents\ProfileStatsData;
use PostProxy\Types\WebhookEvents\PlatformPostData;
use PostProxy\Types\WebhookEvents\CommentCreatedData;

$event = WebhookEvents::parse($request->getContent());
match ($event->type) {
    'profile.stats' => /** @var ProfileStatsData $d */ $d = $event->data,
    'platform_post.published' => /** @var PlatformPostData $d */ $d = $event->data,
    'comment.created' => /** @var CommentCreatedData $d */ $d = $event->data,
    default => null,
};
```

### Comments

```php
// List comments on a post (paginated)
$comments = $client->comments()->list('post-id', profileId: 'profile-id');
foreach ($comments->data as $comment) {
    echo "{$comment->authorUsername}: {$comment->body}\n";
    foreach ($comment->replies as $reply) {
        echo "  {$reply->authorUsername}: {$reply->body}\n";
    }
}

// List with pagination
$comments = $client->comments()->list('post-id', profileId: 'profile-id', page: 2, perPage: 10);

// Get a single comment
$comment = $client->comments()->get('post-id', 'comment-id', profileId: 'profile-id');

// Create a comment
$comment = $client->comments()->create('post-id', profileId: 'profile-id', text: 'Great post!');

// Reply to a comment
$reply = $client->comments()->create('post-id', profileId: 'profile-id', text: 'Thanks!', parentId: 'comment-id');

// Delete a comment
$result = $client->comments()->delete('post-id', 'comment-id', profileId: 'profile-id');
echo $result->accepted; // true

// Hide / unhide a comment
$client->comments()->hide('post-id', 'comment-id', profileId: 'profile-id');
$client->comments()->unhide('post-id', 'comment-id', profileId: 'profile-id');

// Like / unlike a comment
$client->comments()->like('post-id', 'comment-id', profileId: 'profile-id');
$client->comments()->unlike('post-id', 'comment-id', profileId: 'profile-id');
```

### Profile comments (Google Business reviews)

Profile-level comments expose Google Business reviews and replies. Reviews are user-generated — the SDK lets you list/get them and reply to or delete your own replies. Reviews sync twice daily.

```php
// List reviews for a profile (paginated)
$reviews = $client->profileComments()->list('profile-id');
foreach ($reviews->data as $review) {
    echo "{$review->authorUsername}: {$review->body}\n";
    foreach ($review->replies as $reply) {
        echo "  reply: {$reply->body}\n";
    }
}

// Filter by placement (location)
$reviews = $client->profileComments()->list('profile-id', placementId: 'accounts/123/locations/456');

// Get a single review
$review = $client->profileComments()->get('profile-id', 'review-id');

// Reply to a review (parentId is the review id)
$reply = $client->profileComments()->create('profile-id', parentId: 'review-id', text: 'Thanks for visiting!');

// Delete your reply
$client->profileComments()->delete('profile-id', 'reply-id');
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

// Profile stats timeseries — placementId required for facebook, linkedin, telegram
$stats = $client->profiles()->getProfileStats(
    'prof_li_001',
    placementId: '108520199',
    from: '2026-04-01T00:00:00Z',
);
foreach ($stats->data->records as $r) {
    echo $r->recordedAt . ': ' . $r->stats['followerCount'] . "\n";
}

// Bluesky — no placements
$bsky = $client->profiles()->getProfileStats('prof_bsky_001');
echo end($bsky->data->records)->stats['followersCount'];
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

// BlueSky — app password (synchronous)
$bsky = $client->profileGroups()->connectBluesky(
    'pg-id',
    identifier: 'yourname.bsky.social',
    appPassword: 'xxxx-xxxx-xxxx-xxxx',
);
echo $bsky->profile->id;

// Telegram — bring-your-own-bot. Channels populate asynchronously; poll
// placements until non-empty.
$tg = $client->profileGroups()->connectTelegram(
    'pg-id',
    botToken: '123456789:ABCdef-GhIJklMnOpQrStUvWxYz',
);
echo $tg->nextStep;

$placements = [];
while (empty($placements)) {
    $placements = $client->profiles()->placements($tg->profile->id)->data;
    if (empty($placements)) sleep(3);
}
```

## Platform Parameters

```php
use PostProxy\Types\PlatformParams\PlatformParams;
use PostProxy\Types\PlatformParams\FacebookParams;
use PostProxy\Types\PlatformParams\InstagramParams;
use PostProxy\Types\PlatformParams\TelegramParams;
use PostProxy\Types\PlatformParams\BlueskyParams;

$platforms = new PlatformParams([
    'facebook' => new FacebookParams(['format' => 'post', 'first_comment' => 'Hi!']),
    'instagram' => new InstagramParams(['format' => 'reel']),
    'bluesky' => new BlueskyParams(['format' => 'post']),
    'telegram' => new TelegramParams([
        'chat_id' => '-1001234567890',
        'parse_mode' => 'HTML',
        'disable_link_preview' => true,
    ]),
]);

$post = $client->posts()->create('Hello!', profiles: ['prof-1'], platforms: $platforms);
```

Supported platforms: `facebook`, `instagram`, `tiktok`, `linkedin`, `youtube`, `twitter`, `threads`, `pinterest`, `bluesky`, `telegram`, `google_business`. Telegram requires a `chat_id` per post — list channels with `$client->profiles()->placements($profileId)`.

#### Google Business

Google Business posts use the `googleBusiness` property on `PlatformParams` (a plain associative array). The `location_id` is the location resource path returned by `$client->profiles()->placements()`. Supported formats: `standard`, `event`, `offer`. CTA actions: `LEARN_MORE`, `BOOK`, `ORDER`, `SHOP`, `SIGN_UP`, `CALL`. Media is limited to one image (≤5 MB).

```php
use PostProxy\Types\PlatformParams\PlatformParams;

$platforms = new PlatformParams([
    'google_business' => [
        'format' => 'standard',
        'location_id' => 'accounts/123/locations/456',
        'cta_action_type' => 'LEARN_MORE',
        'cta_url' => 'https://example.com',
    ],
]);
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
