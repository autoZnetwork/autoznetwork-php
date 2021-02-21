<?php

namespace AutozNetwork\Tests;

use AutozNetwork\AutozNetworkApp;
use PHPUnit\Framework\TestCase;

class AutozNetworkAppTest extends TestCase
{
    /**
     * @var AutozNetworkApp
     */
    private $app;

    protected function setUp(): void
    {
        $this->app = new AutozNetworkApp('id', 'secret');
    }

    public function testGetId()
    {
        $this->assertEquals('id', $this->app->getId());
    }

    public function testGetSecret()
    {
        $this->assertEquals('secret', $this->app->getSecret());
    }

    public function testAnAppAccessTokenCanBeGenerated()
    {
        $accessToken = $this->app->getAccessToken();

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('id|secret', (string)$accessToken);
    }

    public function testSerialization()
    {
        $newApp = unserialize(serialize($this->app));

        $this->assertInstanceOf('AutozNetwork\AutozNetworkApp', $newApp);
        $this->assertEquals('id', $newApp->getId());
        $this->assertEquals('secret', $newApp->getSecret());
    }

    public function testOverflowIntegersWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        new AutozNetworkApp(PHP_INT_MAX + 1, "foo");
    }

    public function testUnserializedIdsWillBeString()
    {
        $newApp = unserialize(serialize(new AutozNetworkApp(1, "foo")));

        $this->assertSame('1', $newApp->getId());
    }
}
