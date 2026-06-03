<?php

namespace PostProxy\Tests;

use PostProxy\Exceptions\WebhookParseException;
use PostProxy\Types\Message;
use PostProxy\Types\WebhookEvents\CommentCreatedData;
use PostProxy\Types\WebhookEvents\MediaFailedData;
use PostProxy\Types\WebhookEvents\MessageEventData;
use PostProxy\Types\WebhookEvents\PlatformPostData;
use PostProxy\Types\WebhookEvents\PostImportedData;
use PostProxy\Types\WebhookEvents\PostProcessedData;
use PostProxy\Types\WebhookEvents\ProfileCommentCreatedData;
use PostProxy\Types\WebhookEvents\ProfileEventData;
use PostProxy\Types\WebhookEvents\ProfileStatsData;
use PostProxy\Types\WebhookEvents\ReactionEventData;
use PostProxy\WebhookEvents;

class WebhookEventsTest extends TestCase
{
    private function envelope(string $type, array $data): string
    {
        return json_encode([
            'id' => 'evt_1',
            'type' => $type,
            'created_at' => '2026-05-12T00:00:00Z',
            'data' => $data,
        ]);
    }

    public function test_parses_post_processed(): void
    {
        $event = WebhookEvents::parse($this->envelope('post.processed', [
            'id' => 'p1', 'body' => 'hi', 'status' => 'processed',
            'scheduled_at' => null, 'created_at' => '2026-05-12T00:00:00Z',
            'platforms' => [['id' => 'pf1', 'platform' => 'twitter', 'name' => 'X']],
        ]));
        $this->assertSame('post.processed', $event->type);
        $this->assertInstanceOf(PostProcessedData::class, $event->data);
        $this->assertSame('twitter', $event->data->platforms[0]->platform);
    }

    public function test_parses_post_imported(): void
    {
        $event = WebhookEvents::parse($this->envelope('post.imported', [
            'id' => 'p1', 'body' => 'x', 'source' => 'imported',
            'posted_at' => '2026-05-11T00:00:00Z', 'created_at' => '2026-05-12T00:00:00Z',
            'platform' => 'instagram',
            'profile' => ['id' => 'pf1', 'name' => 'Acme', 'platform' => 'instagram'],
            'platform_post_id' => 'ig123', 'public_id' => 'DEF',
        ]));
        $this->assertInstanceOf(PostImportedData::class, $event->data);
        $this->assertSame('ig123', $event->data->platformPostId);
        $this->assertSame('instagram', $event->data->profile->platform);
    }

    public function test_parses_platform_post_variants(): void
    {
        foreach ([
            'platform_post.published',
            'platform_post.failed',
            'platform_post.failed_waiting_for_retry',
            'platform_post.insights',
        ] as $type) {
            $event = WebhookEvents::parse($this->envelope($type, [
                'id' => 'pp1', 'post_id' => 'p1', 'platform' => 'twitter',
                'profile_id' => 'pf1', 'profile_name' => 'X', 'status' => 'published',
                'error' => null, 'error_details' => null, 'platform_id' => 'tw_999',
            ]));
            $this->assertSame($type, $event->type);
            $this->assertInstanceOf(PlatformPostData::class, $event->data);
        }
    }

    public function test_parses_profile_events(): void
    {
        foreach (['profile.connected', 'profile.disconnected'] as $type) {
            $event = WebhookEvents::parse($this->envelope($type, [
                'id' => 'pf1', 'name' => 'X', 'platform' => 'twitter',
                'profile_group_id' => 'g1', 'status' => 'active',
                'uid' => 'u1', 'username' => 'handle',
            ]));
            $this->assertInstanceOf(ProfileEventData::class, $event->data);
        }
    }

    public function test_parses_profile_stats(): void
    {
        $event = WebhookEvents::parse($this->envelope('profile.stats', [
            'profile_id' => 'pf1', 'platform' => 'linkedin',
            'placement_id' => 'org_1', 'stats' => ['followerCount' => 4567],
            'recorded_at' => '2026-05-12T00:00:00Z',
        ]));
        $this->assertInstanceOf(ProfileStatsData::class, $event->data);
        $this->assertSame('org_1', $event->data->placementId);
        $this->assertSame(4567, $event->data->stats['followerCount']);
    }

    public function test_parses_media_failed(): void
    {
        $event = WebhookEvents::parse($this->envelope('media.failed', [
            'id' => 'm1', 'post_id' => 'p1', 'content_type' => 'image/jpeg',
            'status' => 'failed', 'error_message' => 'broken',
        ]));
        $this->assertInstanceOf(MediaFailedData::class, $event->data);
    }

    public function test_parses_comment_created(): void
    {
        $event = WebhookEvents::parse($this->envelope('comment.created', [
            'id' => 'c1', 'post_id' => 'p1', 'platform_post_id' => 'pp1',
            'platform' => 'instagram', 'external_id' => 'ig_c',
            'parent_external_id' => null, 'body' => 'great', 'status' => 'published',
            'author_external_id' => 'u', 'author_name' => 'Jane',
            'author_username' => 'jane', 'author_avatar_url' => null,
            'like_count' => 0, 'reply_count' => 0, 'is_hidden' => false,
            'permalink' => null, 'platform_data' => null, 'posted_at' => null,
            'created_at' => '2026-05-12T00:00:00Z',
        ]));
        $this->assertInstanceOf(CommentCreatedData::class, $event->data);
        $this->assertSame('Jane', $event->data->authorName);
    }

    private function message(array $overrides = []): array
    {
        return array_merge([
            'id' => 'msg_1',
            'chat_id' => 'chat_1',
            'external_id' => 'mid.1',
            'direction' => 'inbound',
            'body' => 'hello',
            'status' => 'received',
            'reactions' => [],
            'attachments' => [],
            'is_unsupported' => false,
            'created_at' => '2026-06-01T15:00:00Z',
        ], $overrides);
    }

    public function test_parses_message_events(): void
    {
        foreach ([
            'message.received',
            'message.sent',
            'message.delivered',
            'message.read',
            'message.edited',
            'message.deleted',
            'message.failed_waiting_for_retry',
            'message.failed',
        ] as $type) {
            $event = WebhookEvents::parse($this->envelope($type, [
                'message' => $this->message(),
            ]));
            $this->assertSame($type, $event->type);
            $this->assertInstanceOf(MessageEventData::class, $event->data);
            $this->assertInstanceOf(Message::class, $event->data->message);
            $this->assertSame('msg_1', $event->data->message->id);
        }
    }

    public function test_parses_reaction_received(): void
    {
        $event = WebhookEvents::parse($this->envelope('reaction.received', [
            'message' => $this->message([
                'reactions' => [
                    ['sender_external_id' => 'psid_123', 'emoji' => '❤️', 'reaction' => 'love', 'at' => '2026-06-01T15:02:00Z'],
                ],
            ]),
            'sender_external_id' => 'psid_123',
            'action' => 'react',
            'reaction' => 'love',
            'emoji' => '❤️',
            'occurred_at' => '2026-06-01T15:02:00Z',
        ]));

        $this->assertInstanceOf(ReactionEventData::class, $event->data);
        $this->assertSame('react', $event->data->action);
        $this->assertInstanceOf(Message::class, $event->data->message);
        $this->assertSame('love', $event->data->message->reactions[0]->reaction);
    }

    public function test_parses_profile_comment_created(): void
    {
        $event = WebhookEvents::parse($this->envelope('profile_comment.created', [
            'id' => 'abc123',
            'profile_id' => 'prof123',
            'platform' => 'google_business',
            'placement_id' => 'accounts/1/locations/2',
            'external_id' => 'accounts/1/locations/2/reviews/A',
            'parent_external_id' => null,
            'body' => 'Great coffee!',
            'status' => 'synced',
            'author_username' => 'Jane D.',
            'author_avatar_url' => null,
            'platform_data' => ['star_rating' => 5],
            'posted_at' => '2026-05-10T11:55:00Z',
            'created_at' => '2026-05-13T18:00:00Z',
        ]));

        $this->assertInstanceOf(ProfileCommentCreatedData::class, $event->data);
        $this->assertSame('prof123', $event->data->profileId);
        $this->assertSame(5, $event->data->platformData['star_rating']);
    }

    public function test_accepts_array_body(): void
    {
        $event = WebhookEvents::parse([
            'id' => 'e', 'type' => 'media.failed', 'created_at' => 'x',
            'data' => [
                'id' => 'm1', 'post_id' => 'p1', 'content_type' => 'image/jpeg',
                'status' => 'failed', 'error_message' => 'broken',
            ],
        ]);
        $this->assertSame('media.failed', $event->type);
    }

    public function test_raises_on_unknown_type(): void
    {
        $this->expectException(WebhookParseException::class);
        WebhookEvents::parse($this->envelope('foo.bar', []));
    }

    public function test_raises_on_invalid_json(): void
    {
        $this->expectException(WebhookParseException::class);
        WebhookEvents::parse('not json{');
    }
}
