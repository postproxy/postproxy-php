<?php

namespace PostProxy;

class Constants
{
    public const DEFAULT_BASE_URL = 'https://api.postproxy.dev';

    public const VERSION = '1.11.0';

    public const PLATFORMS = [
        'facebook', 'instagram', 'tiktok', 'linkedin',
        'youtube', 'twitter', 'threads', 'pinterest',
        'bluesky', 'telegram', 'google_business',
    ];

    public const PROFILE_STATUSES = ['active', 'expired', 'inactive'];

    public const POST_STATUSES = ['pending', 'draft', 'processing', 'processed', 'scheduled', 'media_processing_failed'];

    public const MEDIA_STATUSES = ['pending', 'processed', 'failed'];

    public const PLATFORM_POST_STATUSES = ['pending', 'processing', 'published', 'failed', 'deleted'];

    public const INSTAGRAM_FORMATS = ['post', 'reel', 'story'];
    public const FACEBOOK_FORMATS = ['post', 'story', 'reel'];
    public const TIKTOK_FORMATS = ['video', 'image'];
    public const LINKEDIN_FORMATS = ['post'];
    public const YOUTUBE_FORMATS = ['post'];
    public const PINTEREST_FORMATS = ['pin'];
    public const THREADS_FORMATS = ['post'];
    public const TWITTER_FORMATS = ['post', 'poll'];
    public const BLUESKY_FORMATS = ['post'];
    public const TELEGRAM_FORMATS = ['post'];

    public const TIKTOK_PRIVACIES = [
        'PUBLIC_TO_EVERYONE', 'MUTUAL_FOLLOW_FRIENDS',
        'FOLLOWER_OF_CREATOR', 'SELF_ONLY',
    ];

    public const YOUTUBE_PRIVACIES = ['public', 'unlisted', 'private'];

    public const TELEGRAM_PARSE_MODES = ['HTML', 'MarkdownV2'];

    public const MESSAGE_DIRECTIONS = ['inbound', 'outbound'];

    public const MESSAGE_STATUSES = [
        'pending',
        'published',
        'failed_waiting_for_retry',
        'failed',
        'received',
    ];

    public const WEBHOOK_EVENT_TYPES = [
        'post.processed',
        'post.imported',
        'platform_post.published',
        'platform_post.failed',
        'platform_post.failed_waiting_for_retry',
        'platform_post.insights',
        'profile.connected',
        'profile.disconnected',
        'profile.stats',
        'media.failed',
        'comment.created',
        'profile_comment.created',
        'message.received',
        'message.sent',
        'message.delivered',
        'message.read',
        'message.edited',
        'message.deleted',
        'message.failed_waiting_for_retry',
        'message.failed',
        'reaction.received',
    ];
}
