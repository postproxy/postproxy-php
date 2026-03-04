<?php

namespace PostProxy\Types;

class Post extends Model
{
    public ?string $id = null;
    public ?string $body = null;
    public ?string $status = null;
    public mixed $scheduledAt = null;
    public mixed $createdAt = null;
    public array $media = [];
    public array $platforms = [];
    public array $thread = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->scheduledAt = self::parseTime($this->scheduledAt);
        $this->createdAt = self::parseTime($this->createdAt);
        $this->media = array_map(function ($m) {
            if ($m instanceof Media) {
                return $m;
            }
            return new Media($m);
        }, $this->media ?? []);
        $this->platforms = array_map(function ($p) {
            if ($p instanceof PlatformResult) {
                return $p;
            }
            return new PlatformResult($p);
        }, $this->platforms ?? []);
        $this->thread = array_map(function ($t) {
            if ($t instanceof ThreadChild) {
                return $t;
            }
            return new ThreadChild($t);
        }, $this->thread ?? []);
    }

    private static function parseTime(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        return new \DateTimeImmutable((string) $value);
    }
}
