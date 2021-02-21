<?php

namespace AutozNetwork\Tests\HttpClients;

use AutozNetwork\HttpClients\AutozNetworkCurlHttpClient;
use Mockery as m;

class AutozNetworkCurlHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var \AutozNetwork\HttpClients\AutozNetworkCurl
     */
    protected $curlMock;

    /**
     * @var AutozNetworkCurlHttpClient
     */
    protected $curlClient;

    const CURL_VERSION_STABLE = 0x072400;
    const CURL_VERSION_BUGGY = 0x071400;

    protected function setUp(): void
    {
        if (! extension_loaded('curl')) {
            $this->markTestSkipped('cURL must be installed to test cURL client handler.');
        }
        $this->curlMock = m::mock('AutozNetwork\HttpClients\AutozNetworkCurl');
        $this->curlClient = new AutozNetworkCurlHttpClient($this->curlMock);
    }

    public function testCanOpenGetCurlConnection()
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->with(m::on(function ($arg) {

                // array_diff() will sometimes trigger error on child-arrays
                if (['X-Foo-Header: X-Bar'] !== $arg[CURLOPT_HTTPHEADER]) {
                    return false;
                }
                unset($arg[CURLOPT_HTTPHEADER]);

                $caInfo = array_diff($arg, [
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_URL => 'http://foo.com',
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 123,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

//                if (count($caInfo) !== 1) {
//                    return false;
//                }
//
//                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
//                    return false;
//                }

                return true;
            }))
            ->once()
            ->andReturn(null);

        $this->curlClient->openConnection('http://foo.com', 'GET', 'foo_body', ['X-Foo-Header' => 'X-Bar'], 123);

        $this->expectNotToPerformAssertions();
    }

    public function testCanOpenCurlConnectionWithPostBody()
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->with(m::on(function ($arg) {

                // array_diff() will sometimes trigger error on child-arrays
                if ([] !== $arg[CURLOPT_HTTPHEADER]) {
                    return false;
                }
                unset($arg[CURLOPT_HTTPHEADER]);

                $caInfo = array_diff($arg, [
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_URL => 'http://bar.com',
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_POSTFIELDS => 'baz=bar',
                ]);

//                if (count($caInfo) !== 1) {
//                    return false;
//                }
//
//                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
//                    return false;
//                }

                return true;
            }))
            ->once()
            ->andReturn(null);

        $this->curlClient->openConnection('http://bar.com', 'POST', 'baz=bar', [], 60);

        $this->expectNotToPerformAssertions();
    }

    public function testCanCloseConnection()
    {
        $this->curlMock
            ->shouldReceive('close')
            ->once()
            ->andReturn(null);

        $this->curlClient->closeConnection();

        $this->expectNotToPerformAssertions();
    }

    public function testIsolatesTheHeaderAndBody()
    {
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($this->fakeRawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeader, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeader, trim($this->fakeRawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeaders()
    {
        $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeadersWithCurlBug()
    {
        $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeadersWithCurlBug2()
    {
        $rawHeader = $this->fakeRawProxyHeader2 . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesRedirectHeaders()
    {
        $rawHeader = $this->fakeRawRedirectHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        list($rawHeaders, $rawBody) = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testCanSendNormalRequest()
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($this->fakeRawHeader . $this->fakeRawBody);
        $this->curlMock
            ->shouldReceive('errno')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('close')
            ->once()
            ->andReturn(null);

        $response = $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);

        $this->assertInstanceOf('AutozNetwork\Http\GraphRawResponse', $response);
        $this->assertEquals($this->fakeRawBody, $response->getBody());
        $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    /**
     * @expectedException \AutozNetwork\Exceptions\AutozNetworkSDKException
     */
    public function testThrowsExceptionOnClientError()
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn(false);
        $this->curlMock
            ->shouldReceive('errno')
            ->once()
            ->andReturn(123);
        $this->curlMock
            ->shouldReceive('error')
            ->once()
            ->andReturn('Foo error');

        $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);
    }
}
