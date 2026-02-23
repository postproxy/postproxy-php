<?php

namespace PostProxy\Tests;

use PostProxy\Types\ConnectionResponse;
use PostProxy\Types\DeleteResponse;
use PostProxy\Types\ListResponse;
use PostProxy\Types\ProfileGroup;

class ProfileGroupsTest extends TestCase
{
    public function testListReturnsProfileGroups(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'data' => [
                ['id' => 'pg-1', 'name' => 'My Group', 'profiles_count' => 3],
            ],
        ]);

        $result = $client->profileGroups()->list();

        $this->assertInstanceOf(ListResponse::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(ProfileGroup::class, $result->data[0]);
        $this->assertEquals('pg-1', $result->data[0]->id);
        $this->assertEquals('My Group', $result->data[0]->name);
        $this->assertEquals(3, $result->data[0]->profilesCount);
    }

    public function testGetReturnsSingleProfileGroup(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['id' => 'pg-1', 'name' => 'My Group', 'profiles_count' => 5]);

        $group = $client->profileGroups()->get('pg-1');

        $this->assertEquals('pg-1', $group->id);
        $this->assertEquals(5, $group->profilesCount);
    }

    public function testCreateProfileGroup(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['id' => 'pg-new', 'name' => 'New Group', 'profiles_count' => 0]);

        $group = $client->profileGroups()->create('New Group');

        $this->assertEquals('pg-new', $group->id);
        $this->assertEquals('New Group', $group->name);

        $body = $this->lastRequestBody();
        $this->assertEquals('New Group', $body['name']);
    }

    public function testDeleteProfileGroup(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, ['deleted' => true]);

        $result = $client->profileGroups()->delete('pg-1');

        $this->assertInstanceOf(DeleteResponse::class, $result);
        $this->assertTrue($result->deleted);
    }

    public function testInitializeConnection(): void
    {
        $client = $this->mockClient();
        $this->queueResponse(200, [
            'url' => 'https://oauth.example.com/connect',
            'success' => true,
        ]);

        $result = $client->profileGroups()->initializeConnection(
            'pg-1',
            platform: 'instagram',
            redirectUrl: 'https://myapp.com/callback',
        );

        $this->assertInstanceOf(ConnectionResponse::class, $result);
        $this->assertEquals('https://oauth.example.com/connect', $result->url);
        $this->assertTrue($result->success);

        $body = $this->lastRequestBody();
        $this->assertEquals('instagram', $body['platform']);
        $this->assertEquals('https://myapp.com/callback', $body['redirect_url']);
    }
}
