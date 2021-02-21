<?php

namespace AutozNetwork\Tests\Helpers;

use AutozNetwork\AutozNetwork;
use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkClient;
use AutozNetwork\Helpers\AutozNetworkRedirectLoginHelper;
use AutozNetwork\PersistentData\AutozNetworkMemoryPersistentDataHandler;
use AutozNetwork\Tests\Fixtures\FooPseudoRandomStringGenerator;
use AutozNetwork\Tests\Fixtures\FooRedirectLoginOAuth2Client;
use PHPUnit\Framework\TestCase;

class AutozNetworkRedirectLoginHelperTest extends TestCase
{
    /**
     * @var AutozNetworkMemoryPersistentDataHandler
     */
    protected $persistentDataHandler;

    /**
     * @var AutozNetworkRedirectLoginHelper
     */
    protected $redirectLoginHelper;

    const REDIRECT_URL = 'http://invalid.zzz';
    const FOO_CODE = "foo_code";
    const FOO_ENFORCE_HTTPS = "foo_enforce_https";
    const FOO_STATE = "foo_state";
    const FOO_PARAM = "some_param=blah";

    protected function setUp(): void
    {
        $this->persistentDataHandler = new AutozNetworkMemoryPersistentDataHandler();

        $app = new AutozNetworkApp('123', 'foo_app_secret');
        $oAuth2Client = new FooRedirectLoginOAuth2Client($app, new AutozNetworkClient(), 'v1337');
        $this->redirectLoginHelper = new AutozNetworkRedirectLoginHelper($oAuth2Client, $this->persistentDataHandler);
    }

    public function testLoginURL()
    {
        $scope = ['foo', 'bar'];
        $loginUrl = $this->redirectLoginHelper->getLoginUrl(self::REDIRECT_URL, $scope);

        $expectedUrl = 'https://www.AutozNetwork.com/v1337/dialog/oauth?';
        $this->assertTrue(strpos($loginUrl, $expectedUrl) === 0, 'Unexpected base login URL returned from getLoginUrl().');

        $params = [
            'client_id' => '123',
            'redirect_uri' => self::REDIRECT_URL,
            'state' => $this->persistentDataHandler->get('state'),
            'sdk' => 'php-sdk-' . AutozNetwork::VERSION,
            'scope' => implode(',', $scope),
        ];
        foreach ($params as $key => $value) {
            $this->assertContains($key . '=' . urlencode($value), $loginUrl);
        }
    }

    public function testLogoutURL()
    {
        $logoutUrl = $this->redirectLoginHelper->getLogoutUrl('foo_token', self::REDIRECT_URL);
        $expectedUrl = 'https://www.AutozNetwork.com/logout.php?';
        $this->assertTrue(strpos($logoutUrl, $expectedUrl) === 0, 'Unexpected base logout URL returned from getLogoutUrl().');

        $params = [
            'next' => self::REDIRECT_URL,
            'access_token' => 'foo_token',
        ];
        foreach ($params as $key => $value) {
            $this->assertTrue(
                strpos($logoutUrl, $key . '=' . urlencode($value)) !== false
            );
        }
    }

    public function testAnAccessTokenCanBeObtainedFromRedirect()
    {
        $this->persistentDataHandler->set('state', static::FOO_STATE);

        $_GET['code'] = static::FOO_CODE;
        $_GET['enforce_https'] = static::FOO_ENFORCE_HTTPS;
        $_GET['state'] = static::FOO_STATE;

        $fullUrl = self::REDIRECT_URL . '?state=' . static::FOO_STATE . '&enforce_https=' . static::FOO_ENFORCE_HTTPS . '&code=' . static::FOO_CODE . '&' . static::FOO_PARAM;

        $accessToken = $this->redirectLoginHelper->getAccessToken($fullUrl);

        // 'code', 'enforce_https' and 'state' should be stripped from the URL
        $expectedUrl = self::REDIRECT_URL . '?' . static::FOO_PARAM;
        $expectedString = 'foo_token_from_code|' . static::FOO_CODE . '|' . $expectedUrl;

        $this->assertEquals($expectedString, $accessToken->getValue());
    }

    public function testACustomCsprsgCanBeInjected()
    {
        $app = new AutozNetworkApp('123', 'foo_app_secret');
        $accessTokenClient = new FooRedirectLoginOAuth2Client($app, new AutozNetworkClient(), 'v1337');
        $fooPrsg = new FooPseudoRandomStringGenerator();
        $helper = new AutozNetworkRedirectLoginHelper($accessTokenClient, $this->persistentDataHandler, null, $fooPrsg);

        $loginUrl = $helper->getLoginUrl(self::REDIRECT_URL);

        $this->assertContains('state=csprs123', $loginUrl);
    }

    public function testThePseudoRandomStringGeneratorWillAutoDetectCsprsg()
    {
        $this->assertInstanceOf(
            'AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorInterface',
            $this->redirectLoginHelper->getPseudoRandomStringGenerator()
        );
    }
}
