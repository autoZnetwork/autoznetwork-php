<?php

namespace AutozNetwork\Tests\Helpers;

use AutozNetwork\AutozNetworkApp;
use AutozNetwork\Tests\Fixtures\FooSignedRequestHelper;
use AutozNetwork\Tests\Fixtures\FooSignedRequestHelperAutozNetworkClient;
use PHPUnit\Framework\TestCase;

class AutozNetworkSignedRequestFromInputHelperTest extends TestCase
{
    /**
     * @var FooSignedRequestHelper
     */
    protected $helper;

    public $rawSignedRequestAuthorizedWithAccessToken = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';
    public $rawSignedRequestAuthorizedWithCode = 'oBtmZlsFguNQvGRETDYQQu1-PhwcArgbBBEK4urbpRA=.eyJjb2RlIjoiZm9vX2NvZGUiLCJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjMxMDc1MiwidXNlcl9pZCI6IjEyMyJ9';
    public $rawSignedRequestUnauthorized = 'KPlyhz-whtYAhHWr15N5TkbS_avz-2rUJFpFkfXKC88=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwMjU1MTA4Nn0=';

    protected function setUp(): void
    {
        $app = new AutozNetworkApp('123', 'foo_app_secret');
        $this->helper = new FooSignedRequestHelper($app, new FooSignedRequestHelperAutozNetworkClient());
    }

    public function testSignedRequestDataCanBeRetrievedFromPostData()
    {
        $_POST['signed_request'] = 'foo_signed_request';

        $rawSignedRequest = $this->helper->getRawSignedRequestFromPost();

        $this->assertEquals('foo_signed_request', $rawSignedRequest);
    }

    public function testSignedRequestDataCanBeRetrievedFromCookieData()
    {
        $_COOKIE['fbsr_123'] = 'foo_signed_request';

        $rawSignedRequest = $this->helper->getRawSignedRequestFromCookie();

        $this->assertEquals('foo_signed_request', $rawSignedRequest);
    }

    public function testAccessTokenWillBeNullWhenAUserHasNotYetAuthorizedTheApp()
    {
        $this->helper->instantiateSignedRequest($this->rawSignedRequestUnauthorized);
        $accessToken = $this->helper->getAccessToken();

        $this->assertNull($accessToken);
    }

    public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsAnAccessToken()
    {
        $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithAccessToken);
        $accessToken = $this->helper->getAccessToken();

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_token', $accessToken->getValue());
    }

    public function testAnAccessTokenCanBeInstantiatedWhenRedirectReturnsACode()
    {
        $this->helper->instantiateSignedRequest($this->rawSignedRequestAuthorizedWithCode);
        $accessToken = $this->helper->getAccessToken();

        $this->assertInstanceOf('AutozNetwork\Authentication\AccessToken', $accessToken);
        $this->assertEquals('foo_access_token_from:foo_code', $accessToken->getValue());
    }
}
