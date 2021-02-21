<?php

namespace AutozNetwork\Tests\Authentication;

use AutozNetwork\AutozNetwork;
use AutozNetwork\AutozNetworkApp;
use AutozNetwork\Authentication\OAuth2Client;
use PHPUnit\Framework\TestCase;

class OAuth2ClientTest extends TestCase
{

    /**
     * @const The foo Rest version
     */
    const TESTING_REST_VERSION = 'v1337';

    /**
     * @var FooAutozNetworkClientForOAuth2Test
     */
    protected $client;

    /**
     * @var OAuth2Client
     */
    protected $oauth;

    protected function setUp(): void
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $this->client = new FooAutozNetworkClientForOAuth2Test();
        $this->oauth = new OAuth2Client($app, $this->client, static::TESTING_REST_VERSION);
    }

    public function testCanGetMetadataFromAnAccessToken()
    {
        $this->client->setMetadataResponse();

        $metadata = $this->oauth->debugToken('baz_token');

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessTokenMetadata', $metadata);
        $this->assertEquals('444', $metadata->getUserId());

        $expectedParams = [
            'input_token' => 'baz_token',
            'access_token' => '123|foo_secret',
            'appsecret_proof' => 'de753c58fd58b03afca2340bbaeb4ecf987b5de4c09e39a63c944dd25efbc234',
        ];

        $request = $this->oauth->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/debug_token', $request->getEndpoint());
        $this->assertEquals($expectedParams, $request->getParams());
        $this->assertEquals(static::TESTING_REST_VERSION, $request->getGraphVersion());
    }

    public function testCanBuildAuthorizationUrl()
    {
        $scope = ['email', 'base_foo'];
        $authUrl = $this->oauth->getAuthorizationUrl('https://foo.bar', 'foo_state', $scope, ['foo' => 'bar'], '*');

        $this->assertStringContainsString('*', $authUrl);

        $expectedUrl = 'https://www.autoznetwork.com/' . static::TESTING_REST_VERSION . '/dialog/oauth?';
        $this->assertTrue(strpos($authUrl, $expectedUrl) === 0, 'Unexpected base authorization URL returned from getAuthorizationUrl().');

        $params = [
            'client_id' => '123',
            'redirect_uri' => 'https://foo.bar',
            'state' => 'foo_state',
            'sdk' => 'php-sdk-' . AutozNetwork::VERSION,
            'scope' => implode(',', $scope),
            'foo' => 'bar',
        ];
        foreach ($params as $key => $value) {
            $this->assertStringContainsString($key . '=' . urlencode($value), $authUrl);
        }
    }

    public function testCanGetAccessTokenFromCode()
    {
        $this->client->setAccessTokenResponse();

        $accessToken = $this->oauth->getAccessTokenFromCode('bar_code', 'foo_uri');

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('my_access_token', $accessToken->getValue());

        $expectedParams = [
            'code' => 'bar_code',
            'redirect_uri' => 'foo_uri',
            'client_id' => '123',
            'client_secret' => 'foo_secret',
            'access_token' => '123|foo_secret',
            'appsecret_proof' => 'de753c58fd58b03afca2340bbaeb4ecf987b5de4c09e39a63c944dd25efbc234',
        ];

        $request = $this->oauth->getLastRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/oauth/access_token', $request->getEndpoint());
        $this->assertEquals($expectedParams, $request->getParams());
        $this->assertEquals(static::TESTING_REST_VERSION, $request->getGraphVersion());
    }

    public function testCanGetLongLivedAccessToken()
    {
        $this->client->setAccessTokenResponse();

        $accessToken = $this->oauth->getLongLivedAccessToken('short_token');

        $this->assertEquals('my_access_token', $accessToken->getValue());

        $expectedParams = [
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => 'short_token',
            'client_id' => '123',
            'client_secret' => 'foo_secret',
            'access_token' => '123|foo_secret',
            'appsecret_proof' => 'de753c58fd58b03afca2340bbaeb4ecf987b5de4c09e39a63c944dd25efbc234',
        ];

        $request = $this->oauth->getLastRequest();
        $this->assertEquals($expectedParams, $request->getParams());
    }

    public function testCanGetCodeFromLongLivedAccessToken()
    {
        $this->client->setCodeResponse();

        $code = $this->oauth->getCodeFromLongLivedAccessToken('long_token', 'foo_uri');

        $this->assertEquals('my_neat_code', $code);

        $expectedParams = [
            'access_token' => 'long_token',
            'redirect_uri' => 'foo_uri',
            'client_id' => '123',
            'client_secret' => 'foo_secret',
            'appsecret_proof' => '7e91300ea91be4166282611d4fc700b473466f3ea2981dafbf492fc096995bf1',
        ];

        $request = $this->oauth->getLastRequest();
        $this->assertEquals($expectedParams, $request->getParams());
        $this->assertEquals('/oauth/client_code', $request->getEndpoint());
    }
}