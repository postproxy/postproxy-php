<?php

namespace PostProxy\Tests;

use PostProxy\Constants;

class UserAgentTest extends TestCase
{
    public function test_sends_user_agent_header(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['data' => []]);

        $client->profiles()->list();

        $ua = $this->lastRequest()->getHeaderLine('User-Agent');
        $this->assertStringStartsWith('postproxy-php/' . Constants::VERSION, $ua);
        $this->assertStringContainsString('php/', $ua);
    }

    public function test_version_constant_is_bumped(): void
    {
        $this->assertSame('1.10.0', Constants::VERSION);
    }
}
