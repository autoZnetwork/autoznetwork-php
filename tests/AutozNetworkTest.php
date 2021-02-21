<?php

namespace AutozNetwork\Tests;

use AutozNetwork\Authentication\AccessToken;
use AutozNetwork\AutozNetwork;
use AutozNetwork\AutozNetworkClient;
use AutozNetwork\AutozNetworkRequest;
//use AutozNetwork\GraphNodes\GraphEdge;
//use AutozNetwork\Tests\Fixtures\FakeGraphApiForResumableUpload;
use AutozNetwork\Tests\Fixtures\FooBarPseudoRandomStringGenerator;
use AutozNetwork\Tests\Fixtures\FooClientInterface;
use AutozNetwork\Tests\Fixtures\FooPersistentDataInterface;
use AutozNetwork\Tests\Fixtures\FooUrlDetectionInterface;
use AutozNetwork\Exceptions\AutozNetworkSDKException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AutozNetworkTest extends TestCase
{
    protected $config = [
        'app_id' => '1337',
        'app_secret' => 'foo_secret',
    ];

    public function testInstantiatingWithoutAppIdThrows()
    {
        $this->expectException(AutozNetworkSDKException::class);

        // unset value so there is no fallback to test expected Exception
        putenv(AutozNetwork::APP_ID_ENV_NAME.'=');
        $config = [
            'app_secret' => 'foo_secret',
        ];

        new AutozNetwork($config);
    }

    public function testInstantiatingWithoutAppSecretThrows()
    {
        $this->expectException(AutozNetworkSDKException::class);

        // unset value so there is no fallback to test expected Exception
        putenv(AutozNetwork::APP_SECRET_ENV_NAME.'=');
        $config = [
            'app_id' => 'foo_id',
        ];
        new AutozNetwork($config);
    }

    public function testSettingAnInvalidHttpClientHandlerThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $config = array_merge($this->config, [
            'http_client_handler' => 'foo_handler',
        ]);
        new AutozNetwork($config);
    }

    public function testCurlHttpClientHandlerCanBeForced()
    {
        if (! extension_loaded('curl')) {
            $this->markTestSkipped('cURL must be installed to test cURL client handler.');
        }
        $config = array_merge($this->config, [
            'http_client_handler' => 'curl',
        ]);
        $autoz = new AutozNetwork($config);
        $this->assertInstanceOf(
            'AutozNetwork\HttpClients\AutozNetworkCurlHttpClient',
            $autoz->getClient()->getHttpClientHandler()
        );
    }

//    public function testStreamHttpClientHandlerCanBeForced()
//    {
//        $config = array_merge($this->config, [
//            'http_client_handler' => 'stream',
//        ]);
//        $autoz = new AutozNetwork($config);
//        $this->assertInstanceOf(
//            'AutozNetwork\HttpClients\AutozNetworkStreamHttpClient',
//            $autoz->getClient()->getHttpClientHandler()
//        );
//    }

    public function testGuzzleHttpClientHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => 'guzzle',
        ]);
        $autoz = new AutozNetwork($config);
        $this->assertInstanceOf(
            'AutozNetwork\HttpClients\AutozNetworkGuzzleHttpClient',
            $autoz->getClient()->getHttpClientHandler()
        );
    }

    public function testSettingAnInvalidPersistentDataHandlerThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $config = array_merge($this->config, [
            'persistent_data_handler' => 'foo_handler',
        ]);

        new AutozNetwork($config);
    }

    public function testPersistentDataHandlerCanBeForced()
    {
        $config = array_merge($this->config, [
            'persistent_data_handler' => 'memory',
        ]);
        $autoz = new AutozNetwork($config);
        $this->assertInstanceOf(
            'AutozNetwork\PersistentData\AutozNetworkMemoryPersistentDataHandler',
            $autoz->getRedirectLoginHelper()->getPersistentDataHandler()
        );
    }

    public function testSettingAnInvalidUrlHandlerThrows()
    {
        $expectedException = (PHP_MAJOR_VERSION > 5 && class_exists('TypeError'))
            ? 'TypeError'
            : 'PHPUnit_Framework_Error';

        $this->expectException($expectedException);

        $config = array_merge($this->config, [
            'url_detection_handler' => 'foo_handler',
        ]);

        new AutozNetwork($config);
    }

    public function testTheUrlHandlerWillDefaultToTheAutozNetworkImplementation()
    {
        $autoz = new AutozNetwork($this->config);
        $this->assertInstanceOf('AutozNetwork\Url\AutozNetworkUrlDetectionHandler', $autoz->getUrlDetectionHandler());
    }

    public function testAnAccessTokenCanBeSetAsAString()
    {
        $autoz = new AutozNetwork($this->config);
        $autoz->setDefaultAccessToken('foo_token');
        $accessToken = $autoz->getDefaultAccessToken();

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_token', (string)$accessToken);
    }

    public function testAnAccessTokenCanBeSetAsAnAccessTokenEntity()
    {
        $autoz = new AutozNetwork($this->config);
        $autoz->setDefaultAccessToken(new AccessToken('bar_token'));
        $accessToken = $autoz->getDefaultAccessToken();

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('bar_token', (string)$accessToken);
    }

    public function testSettingAnAccessThatIsNotStringOrAccessTokenThrows()
    {
        $this->expectException(InvalidArgumentException::class);

        $config = array_merge($this->config, [
            'default_access_token' => 123,
        ]);

        new AutozNetwork($config);
    }

    public function testCreatingANewRequestWillDefaultToTheProperConfig()
    {
        $config = array_merge($this->config, [
            'default_access_token' => 'foo_token',
            'enable_beta_mode' => true,
            'default_graph_version' => 'v1337',
        ]);
        $autoz = new AutozNetwork($config);

        $request = $autoz->request('FOO_VERB', '/foo');
        $this->assertEquals('1337', $request->getApp()->getId());
        $this->assertEquals('foo_secret', $request->getApp()->getSecret());
        $this->assertEquals('foo_token', (string)$request->getAccessToken());
//        $this->assertEquals('v1337', $request->getGraphVersion());
//        $this->assertEquals(
//            AutozNetworkClient::BASE_REST_URL_BETA,
//            $autoz->getClient()->getBaseGraphUrl()
//        );
    }

    public function testCanInjectCustomHandlers()
    {
        $config = array_merge($this->config, [
            'http_client_handler' => new FooClientInterface(),
            'persistent_data_handler' => new FooPersistentDataInterface(),
            'url_detection_handler' => new FooUrlDetectionInterface(),
            'pseudo_random_string_generator' => new FooBarPseudoRandomStringGenerator(),
        ]);
        $autoz = new AutozNetwork($config);

        $this->assertInstanceOf(
            'AutozNetwork\Tests\Fixtures\FooClientInterface',
            $autoz->getClient()->getHttpClientHandler()
        );
        $this->assertInstanceOf(
            'AutozNetwork\Tests\Fixtures\FooPersistentDataInterface',
            $autoz->getRedirectLoginHelper()->getPersistentDataHandler()
        );
        $this->assertInstanceOf(
            'AutozNetwork\Tests\Fixtures\FooUrlDetectionInterface',
            $autoz->getRedirectLoginHelper()->getUrlDetectionHandler()
        );
        $this->assertInstanceOf(
            'AutozNetwork\Tests\Fixtures\FooBarPseudoRandomStringGenerator',
            $autoz->getRedirectLoginHelper()->getPseudoRandomStringGenerator()
        );
    }
}
