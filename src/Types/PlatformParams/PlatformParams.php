<?php

namespace PostProxy\Types\PlatformParams;

use PostProxy\Types\Model;

class PlatformParams extends Model
{
    public ?FacebookParams $facebook = null;
    public ?InstagramParams $instagram = null;
    public ?TikTokParams $tiktok = null;
    public ?LinkedInParams $linkedin = null;
    public ?YouTubeParams $youtube = null;
    public ?PinterestParams $pinterest = null;
    public ?ThreadsParams $threads = null;
    public ?TwitterParams $twitter = null;

    public function toArray(): array
    {
        $result = [];
        $platforms = [
            'facebook', 'instagram', 'tiktok', 'linkedin',
            'youtube', 'pinterest', 'threads', 'twitter',
        ];

        foreach ($platforms as $platform) {
            $value = $this->$platform;
            if ($value === null) {
                continue;
            }

            $params = $value instanceof Model ? $value->toArray() : $value;
            $result[$platform] = array_filter($params, fn($v) => $v !== null);
        }

        return $result;
    }
}
