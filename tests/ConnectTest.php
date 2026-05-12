<?php

namespace PostProxy\Tests;

use PostProxy\Types\BlueskyConnectionResponse;
use PostProxy\Types\TelegramConnectionResponse;
use PostProxy\Types\SyncProfile;

class ConnectTest extends TestCase
{
    public function test_connect_bluesky(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'success' => true,
            'profile' => [
                'id' => 'pf_bsky_1', 'network' => 'bluesky',
                'name' => 'Jane', 'external_username' => 'jane.bsky.social',
            ],
        ]);

        $result = $client->profileGroups()->connectBluesky(
            'pg-1', 'jane.bsky.social', 'xxxx'
        );

        $this->assertInstanceOf(BlueskyConnectionResponse::class, $result);
        $this->assertTrue($result->success);
        $this->assertInstanceOf(SyncProfile::class, $result->profile);
        $this->assertSame('pf_bsky_1', $result->profile->id);

        $this->assertSame([
            'platform' => 'bluesky',
            'identifier' => 'jane.bsky.social',
            'app_password' => 'xxxx',
        ], $this->lastRequestBody());
    }

    public function test_connect_telegram(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'success' => true,
            'profile' => [
                'id' => 'pf_tg_1', 'network' => 'telegram',
                'name' => 'My Bot', 'external_username' => 'my_bot',
            ],
            'next_step' => 'Add bot as admin',
        ]);

        $result = $client->profileGroups()->connectTelegram('pg-1', '123:ABC');

        $this->assertInstanceOf(TelegramConnectionResponse::class, $result);
        $this->assertStringContainsString('admin', $result->nextStep);

        $this->assertSame([
            'platform' => 'telegram',
            'bot_token' => '123:ABC',
        ], $this->lastRequestBody());
    }
}
