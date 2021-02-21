<?php

namespace AutozNetwork\Tests;

use AutozNetwork\AutozNetwork;
use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkRequest;
//use AutozNetwork\FileUpload\AutozNetworkFile;
//use AutozNetwork\FileUpload\AutozNetworkVideo;
use PHPUnit\Framework\TestCase;

class AutozNetworkRequestTest extends TestCase
{
    public function testAnEmptyRequestEntityCanInstantiate()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app);

        $this->assertInstanceOf('AutozNetwork\AutozNetworkRequest', $request);
    }

    public function testAMissingAccessTokenWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app);

        $request->validateAccessToken();
    }

    public function testAMissingMethodWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app);

        $request->validateMethod();
    }

    public function testAnInvalidMethodWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app, 'foo_token', 'FOO');

        $request->validateMethod();
    }

    public function testGetHeadersWillAutoAppendETag()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app, null, 'GET', '/foo', [], 'fooETag');

        $headers = $request->getHeaders();

        $expectedHeaders = AutozNetworkRequest::getDefaultHeaders();
        $expectedHeaders['If-None-Match'] = 'fooETag';

        $this->assertEquals($expectedHeaders, $headers);
    }

    public function testGetParamsWillAutoAppendAccessTokenAndAppSecretProof()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app, 'foo_token', 'POST', '/foo', ['foo' => 'bar']);

        $params = $request->getParams();

        $this->assertEquals([
            'foo' => 'bar',
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ], $params);
    }

    public function testAnAccessTokenCanBeSetFromTheParams()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $request = new AutozNetworkRequest($app, null, 'POST', '/me', ['access_token' => 'bar_token']);

        $accessToken = $request->getAccessToken();

        $this->assertEquals('bar_token', $accessToken);
    }

    public function testAccessTokenConflictsWillThrow()
    {
        $this->expectException(\AutozNetwork\Exceptions\AutozNetworkSDKException::class);

        $app = new AutozNetworkApp('123', 'foo_secret');
        new AutozNetworkRequest($app, 'foo_token', 'POST', '/me', ['access_token' => 'bar_token']);
    }

    public function testAProperUrlWillBeGenerated()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $getRequest = new AutozNetworkRequest($app, 'foo_token', 'GET', '/foo', ['foo' => 'bar']);

        $getUrl = $getRequest->getUrl();
        $expectedParams = 'foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9';
        $expectedUrl = '/' . AutozNetwork::DEFAULT_REST_VERSION . '/foo?' . $expectedParams;

        $this->assertEquals($expectedUrl, $getUrl);

        $postRequest = new AutozNetworkRequest($app, 'foo_token', 'POST', '/bar', ['foo' => 'bar']);

        $postUrl = $postRequest->getUrl();
        $expectedUrl = '/' . AutozNetwork::DEFAULT_REST_VERSION . '/bar';

        $this->assertEquals($expectedUrl, $postUrl);
    }

    public function testAuthenticationParamsAreStrippedAndReapplied()
    {
        $app = new AutozNetworkApp('123', 'foo_secret');

        $request = new AutozNetworkRequest(
            $app,
            $accessToken = 'foo_token',
            $method = 'GET',
            $endpoint = '/foo',
            $params = [
                'access_token' => 'foo_token',
                'appsecret_proof' => 'bar_app_secret',
                'bar' => 'baz',
            ]
        );

        $url = $request->getUrl();

        $expectedParams = 'bar=baz&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9';
        $expectedUrl = '/' . AutozNetwork::DEFAULT_REST_VERSION . '/foo?' . $expectedParams;
        $this->assertEquals($expectedUrl, $url);

        $params = $request->getParams();

        $expectedParams = [
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'bar' => 'baz',
        ];
        $this->assertEquals($expectedParams, $params);
    }

//    public function testAFileCanBeAddedToParams()
//    {
//        $myFile = new AutozNetworkFile(__DIR__ . '/foo.txt');
//        $params = [
//            'name' => 'Foo Bar',
//            'source' => $myFile,
//        ];
//        $app = new AutozNetworkApp('123', 'foo_secret');
//        $request = new AutozNetworkRequest($app, 'foo_token', 'POST', '/foo/photos', $params);
//
//        $actualParams = $request->getParams();
//
//        $this->assertTrue($request->containsFileUploads());
//        $this->assertFalse($request->containsVideoUploads());
//        $this->assertTrue(! isset($actualParams['source']));
//        $this->assertEquals('Foo Bar', $actualParams['name']);
//    }
//
//    public function testAVideoCanBeAddedToParams()
//    {
//        $myFile = new AutozNetworkVideo(__DIR__ . '/foo.txt');
//        $params = [
//            'name' => 'Foo Bar',
//            'source' => $myFile,
//        ];
//        $app = new AutozNetworkApp('123', 'foo_secret');
//        $request = new AutozNetworkRequest($app, 'foo_token', 'POST', '/foo/videos', $params);
//
//        $actualParams = $request->getParams();
//
//        $this->assertTrue($request->containsFileUploads());
//        $this->assertTrue($request->containsVideoUploads());
//        $this->assertTrue(! isset($actualParams['source']));
//        $this->assertEquals('Foo Bar', $actualParams['name']);
//    }
}
