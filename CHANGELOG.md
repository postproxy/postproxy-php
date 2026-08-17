# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.13.0] - 2026-08-17

### Added

- **Interactive DMs on Facebook and Instagram.** `messages()->send()` accepts **`quickReplies`** (up to 13 tappable chips above the participant's composer, gone once tapped — each `title` + `payload`, typed as the new `QuickReply` with a `QuickReply::make()` factory) and **`buttons`** (up to 3 attached to the message, typed as `MessageButton` with `MessageButton::webUrl()` / `MessageButton::postback()` factories). An optional **`card`** (`MessageCard`: `subtitle`, `imageUrl`, `defaultAction` as `CardDefaultAction`) fills in the rest of the card carrying `buttons`, and requires them. Meta's equivalent of what Telegram has had via `replyMarkup`. Each param takes model instances **or** plain arrays; models serialize with nulls dropped.
- **`Message->tappedAction`** — set on inbound messages created by a tap, typed as the new `TappedAction` (`kind`, `payload`, `title`) with `KIND_QUICK_REPLY` / `KIND_POSTBACK` / `KIND_CALLBACK_QUERY` constants. Present on the `message.*` webhook payloads too, so you no longer dig through `platformData` for the payload you set. Derived rather than stored, so it also resolves for taps recorded earlier, including Instagram ice-breaker taps and Telegram callback queries (`KIND_CALLBACK_QUERY` — the one part of this that isn't Meta-only).
- `Message->quickReplies`, `Message->buttons`, and `Message->card`, echoing back what was sent. All four stay `null` rather than `[]` when the API omits them.
- Quick-replies and buttons examples in `examples/manage_messages.php`, plus the previously undocumented `replyMarkup` / `replyToExternalId` in the README's Direct Messages section.

### Notes

- Buttons are delivered as a Meta generic template and your `body` becomes its element title, so **`body` is capped at 80 characters when buttons are present** — Meta's limit, not ours; longer text is rejected with a `422` naming the length. Buttons cannot be combined with media.
- **Instagram is stricter than Messenger**: it delivers quick replies only on a plain-text message, so `quickReplies` with media or with `buttons` returns `422` on Instagram while both are accepted on Facebook.
- Meta-only — `quickReplies` / `buttons` / `card` return `422` on Telegram and Bluesky chats, where `replyMarkup` remains the Telegram equivalent.
- Validation is server-side and names the offending index (e.g. `buttons[1].url must be an https:// URL`), surfacing as the usual exception for a `422`. The SDK does not duplicate the limits.
- The new params are sent on the JSON path only. To combine quick replies with an attachment, pass `media` as a hosted URL rather than uploading via `mediaFiles`.
- `send()` gained three parameters before `$profileGroupId` / `$idempotencyKey`. Call it with named arguments (as the README and examples do) rather than positionally.

## [1.12.0] - 2026-08-06

### Added

- **Post syncs & backfill.** `profiles()->backfillPosts($id, $from)` walks a profile's feed backwards from the newest post and imports the history behind it; `profiles()->postSyncs($id, ...)` and `profiles()->postSync($id, $postSyncId)` expose every post pull — the one fired on connect, the recurring poll, and backfills — as the new `PostSync` type.
- **`comments()->listAll(postIds:, profiles:, from:, to:, page:, perPage:)`** — comments across every post in the profile group in one request. Flat: replies are their own entries linked by `parentExternalId`, typed as the new `BulkComment` (adds `postId`, `profileId`, `platform`).
- `from:` and `to:` on `comments()->list()`, filtering on when PostProxy received the comment.
- **Idempotency.** Every write method accepts `idempotencyKey:`, sent as the `Idempotency-Key` header, so a dropped connection no longer forces a choice between a duplicate write and a lost one.
- `ConflictException` (409), thrown for a duplicate submission (`$e->response['duplicate_post_id']`), a backfill already running (`$e->response['profile_sync_id']`), or an in-flight idempotency key. Previously these surfaced as a bare `PostProxyException`.
- **Instagram user tags.** `InstagramParams::$userTags` with the new `InstagramUserTag` type (`username`, `x`, `y`, `mediaIndex`) — tag accounts on feed posts, reels, and stories.
- `StatsRecord::$rawStats` — every metric under its original platform name, alongside the normalized `stats`.
- `examples/backfill_posts.php`, and cross-post comment listing in `examples/manage_comments.php`.

### Changed

- LinkedIn post stats now normalize `likes`, `comments`, `shares`, and `clicks` alongside `impressions` (server-side; `stats` was already an open array).
- `HUMAN_AGENT` is now approved on **both** Facebook and Instagram and extends the reply window to 7 days. `messages()->send($chatId, tag: 'HUMAN_AGENT')` is unchanged — see the README for Meta's policy limits.

## [1.11.0] - 2026-07-14

### Added

- `profiles()->iceBreakers($id)`, `profiles()->setIceBreakers($id, $iceBreakers)`, and `profiles()->deleteIceBreakers($id)` for managing Instagram DM ice breakers, with `IceBreaker` and `IceBreakersResponse` types.
- `profiles()->assignPlacementToGroup($id, $placementId, $targetProfileGroupId)` to move a placement (Facebook Page, Telegram channel, GBP location) to another profile group.
- `Placement::$metadata` and `Placement::$profileGroupId` properties.
- Twitter polls: `Constants::TWITTER_FORMATS` now includes `'poll'`, and `TwitterParams` gains `$pollOptions` (2-4 choices, max 25 chars each) and `$pollDurationMinutes` (5-10080).

## [1.10.0] - 2026-06-03

### Added

- Direct Messages API:
  - `Chats` resource (`$client->chats()`): `list`, `create`, `get`, `archive`, `unarchive`.
  - `Messages` resource (`$client->messages()`): `list`, `send` (JSON or multipart media), `get`, `edit`, `react`, `unreact`.
  - New types: `Chat`, `Message`, `Reaction`, `Attachment`.
- `Comments::privateReply()` sends a private DM in reply to a comment and returns a `Message`.
- `Comment::$attachments` (rehydrated to `Attachment[]`) and `Comment::$metadata` (author signals).
- `Constants::MESSAGE_DIRECTIONS` and `Constants::MESSAGE_STATUSES`.
- Ten new webhook event types: `profile_comment.created`, `message.received`, `message.sent`, `message.delivered`, `message.read`, `message.edited`, `message.deleted`, `message.failed_waiting_for_retry`, `message.failed`, `reaction.received`.
- Three new typed webhook payloads: `MessageEventData` (wraps a `Message`, used by all eight `message.*` events), `ReactionEventData` (`reaction.received`), and `ProfileCommentCreatedData` (`profile_comment.created`).

## [1.9.0] - 2026-05-15

### Added

- `google_business` platform value for posts and profiles.
- `ProfileComments` resource: `list`, `get`, `create`, `delete` for review replies via `/api/profiles/:profile_id/comments`. Accessed via `$client->profileComments()`.
- `MediaPlatformError` type and `Media::$platforms` property for per-media platform error reporting.
- `PlatformParams::$googleBusiness` (associative array) for Google Business post parameters.
