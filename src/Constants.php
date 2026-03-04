<?php

namespace PostProxy;

class Constants
{
    public const DEFAULT_BASE_URL = 'https://api.postproxy.dev';

    public const PLATFORMS = [
        'facebook', 'instagram', 'tiktok', 'linkedin',
        'youtube', 'twitter', 'threads', 'pinterest',
    ];

    public const PROFILE_STATUSES = ['active', 'expired', 'inactive'];

    public const POST_STATUSES = ['pending', 'draft', 'processing', 'processed', 'scheduled', 'media_processing_failed'];

    public const MEDIA_STATUSES = ['pending', 'processed', 'failed'];

    public const PLATFORM_POST_STATUSES = ['pending', 'processing', 'published', 'failed', 'deleted'];

    public const INSTAGRAM_FORMATS = ['post', 'reel', 'story'];
    public const FACEBOOK_FORMATS = ['post', 'story'];
    public const TIKTOK_FORMATS = ['video', 'image'];
    public const LINKEDIN_FORMATS = ['post'];
    public const YOUTUBE_FORMATS = ['post'];
    public const PINTEREST_FORMATS = ['pin'];
    public const THREADS_FORMATS = ['post'];
    public const TWITTER_FORMATS = ['post'];

    public const TIKTOK_PRIVACIES = [
        'PUBLIC_TO_EVERYONE', 'MUTUAL_FOLLOW_FRIENDS',
        'FOLLOWER_OF_CREATOR', 'SELF_ONLY',
    ];

    public const YOUTUBE_PRIVACIES = ['public', 'unlisted', 'private'];
}
