<?php

namespace AutozNetwork\Tests;

use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkClient;
use AutozNetwork\AutozNetworkRequest;
use AutozNetwork\Exceptions\AutozNetworkSDKException;
use AutozNetwork\FileUpload\AutozNetworkFile;
// These are needed when you uncomment the HTTP clients below.
use AutozNetwork\HttpClients\AutozNetworkCurlHttpClient;
use AutozNetwork\HttpClients\AutozNetworkGuzzleHttpClient;

use AutozNetwork\HttpClients\AutozNetworkStreamHttpClient;
use AutozNetwork\Tests\Fixtures\MyFooClientHandler;
use PHPUnit\Framework\TestCase;

class AutozNetworkClientTest extends TestCase
{
    /**
     * @var AutozNetworkApp
     */
    public $autozApp;

    /**
     * @var AutozNetworkClient
     */
    public $autozClient;

    /**
     * @var AutozNetworkApp
     */
    public static $testAutozNetworkApp;

    /**
     * @var AutozNetworkClient
     */
    public static $testAutozNetworkClient;

    protected function setUp(): void
    {
        $this->autozApp = new AutozNetworkApp('id', 'shhhh!');
        $this->autozClient = new AutozNetworkClient(new MyFooClientHandler());
    }

    public function testACustomHttpClientCanBeInjected()
    {
        $handler = new MyFooClientHandler();
        $client = new AutozNetworkClient($handler);
        $httpHandler = $client->getHttpClientHandler();

        $this->assertInstanceOf('AutozNetwork\Tests\Fixtures\MyFooClientHandler', $httpHandler);
    }

    public function testTheHttpClientWillFallbackToDefault()
    {
        $client = new AutozNetworkClient();
        $httpHandler = $client->getHttpClientHandler();

        if (function_exists('curl_init')) {
            $this->assertInstanceOf('AutozNetwork\HttpClients\AutozNetworkCurlHttpClient', $httpHandler);
        } else {
            $this->assertInstanceOf('AutozNetwork\HttpClients\AutozNetworkStreamHttpClient', $httpHandler);
        }
    }

    public function testBetaModeCanBeDisabledOrEnabledViaConstructor()
    {
        $client = new AutozNetworkClient(null, false);
        $url = $client->getBaseRestUrl();
        $this->assertEquals(AutozNetworkClient::BASE_REST_API_URL, $url);

        $client = new AutozNetworkClient(null, true);
        $url = $client->getBaseRestUrl();
        $this->assertEquals(AutozNetworkClient::BASE_REST_API_URL_BETA, $url);
    }

    public function testBetaModeCanBeDisabledOrEnabledViaMethod()
    {
        $client = new AutozNetworkClient();
        $client->enableBetaMode(false);
        $url = $client->getBaseRestUrl();
        $this->assertEquals(AutozNetworkClient::BASE_REST_API_URL, $url);

        $client->enableBetaMode(true);
        $url = $client->getBaseRestUrl();
        $this->assertEquals(AutozNetworkClient::BASE_REST_API_URL_BETA, $url);
    }

    public function testAAutozNetworkRequestEntityCanBeUsedToSendARequestToGraph()
    {
        $autozRequest = new AutozNetworkRequest($this->autozApp, 'token', 'GET', '/foo');
        $response = $this->autozClient->sendRequest($autozRequest);

        $this->assertInstanceOf('AutozNetwork\AutozNetworkResponse', $response);
        $this->assertEquals(200, $response->getHttpStatusCode());
        $this->assertEquals('{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}', $response->getBody());
    }

    public function testARequestOfParamsWillBeUrlEncoded()
    {
        $autozRequest = new AutozNetworkRequest($this->autozApp, 'token', 'POST', '/foo', ['foo' => 'bar']);
        $response = $this->autozClient->sendRequest($autozRequest);

        $headersSent = $response->getRequest()->getHeaders();

        $this->assertEquals('application/x-www-form-urlencoded', $headersSent['Content-Type']);
    }

    public function testARequestWithFilesWillBeMultipart()
    {
        $myFile = new AutozNetworkFile(__DIR__ . '/foo.txt');
        $autozRequest = new AutozNetworkRequest($this->autozApp, 'token', 'POST', '/foo', ['file' => $myFile]);
        $response = $this->autozClient->sendRequest($autozRequest);

        $headersSent = $response->getRequest()->getHeaders();

        $this->assertStringContainsString('multipart/form-data; boundary=', $headersSent['Content-Type']);
    }

    public function testAnAutozNetworkRequestValidatesTheAccessTokenWhenOneIsNotProvided()
    {
        $this->expectException('AutozNetwork\Exceptions\AutozNetworkSDKException');

        $autozRequest = new AutozNetworkRequest($this->autozApp, null, 'GET', '/foo');
        $this->autozClient->sendRequest($autozRequest);
    }

    /**
     * @group integration
     */
    public function testCanCreateATestUserAndGetTheProfileAndThenDeleteTheTestUser()
    {
        $this->initializeTestApp();

        // Create a test user
        $testUserPath = '/' . AutozNetworkTestCredentials::$appId . '/accounts/test-users';
        $params = [
            'installed' => true,
            'name' => 'Foo Phpunit User',
            'locale' => 'en_US',
            'permissions' => implode(',', ['read_stream', 'user_photos']),
        ];

        $request = new AutozNetworkRequest(
            static::$testAutozNetworkApp,
            static::$testAutozNetworkApp->getAccessToken(),
            'POST',
            $testUserPath,
            $params
        );
        $response = static::$testAutozNetworkClient->sendRequest($request)->getGraphNode();

        $testUserId = $response->getField('id');
        $testUserAccessToken = $response->getField('access_token');

        // Get the test user's profile
        $request = new AutozNetworkRequest(
            static::$testAutozNetworkApp,
            $testUserAccessToken,
            'GET',
            '/me'
        );
        $graphNode = static::$testAutozNetworkClient->sendRequest($request)->getGraphNode();

        $this->assertInstanceOf('AutozNetwork\GraphNodes\GraphNode', $graphNode);
        $this->assertNotNull($graphNode->getField('id'));
        $this->assertEquals('Foo Phpunit User', $graphNode->getField('name'));

        // Delete test user
        $request = new AutozNetworkRequest(
            static::$testAutozNetworkApp,
            static::$testAutozNetworkApp->getAccessToken(),
            'DELETE',
            '/' . $testUserId
        );
        $graphNode = static::$testAutozNetworkClient->sendRequest($request)->getGraphNode();

        $this->assertTrue($graphNode->getField('success'));
    }

    public function initializeTestApp()
    {
        if (! file_exists(__DIR__ . '/AutozNetworkTestCredentials.php')) {
            throw new AutozNetworkSDKException(
                'You must create a AutozNetworkTestCredentials.php file from AutozNetworkTestCredentials.php.dist'
            );
        }

        if (! strlen(AutozNetworkTestCredentials::$appId) ||
            ! strlen(AutozNetworkTestCredentials::$appSecret)
        ) {
            throw new AutozNetworkSDKException(
                'You must fill out AutozNetworkTestCredentials.php'
            );
        }
        static::$testAutozNetworkApp = new AutozNetworkApp(
            AutozNetworkTestCredentials::$appId,
            AutozNetworkTestCredentials::$appSecret
        );

        // Use default client
        $client = null;

        // Uncomment to enable curl implementation.
        //$client = new AutozNetworkCurlHttpClient();

        // Uncomment to enable stream wrapper implementation.
        //$client = new AutozNetworkStreamHttpClient();

        // Uncomment to enable Guzzle implementation.
        //$client = new AutozNetworkGuzzleHttpClient();

        static::$testAutozNetworkClient = new AutozNetworkClient($client);
    }
}
