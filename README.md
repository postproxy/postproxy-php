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

// Publish a draft
$post = $client->posts()->publishDraft('post-id');

// Delete a post
$result = $client->posts()->delete('post-id');
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
