# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

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
