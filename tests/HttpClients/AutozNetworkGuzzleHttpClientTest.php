<?php

namespace AutozNetwork\Tests\HttpClients;

use AutozNetwork\HttpClients\AutozNetworkGuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
//use GuzzleHttp\Stream\Stream;
use Mockery as m;

class AutozNetworkGuzzleHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleMock;

    /**
     * @var AutozNetworkGuzzleHttpClient
     */
    protected $guzzleClient;

    protected function setUp(): void
    {
        $this->guzzleMock = m::mock('GuzzleHttp\Client');
        $this->guzzleClient = new AutozNetworkGuzzleHttpClient($this->guzzleMock);
    }

    public function testCanSendNormalRequest()
    {
        $request = new Request('GET', 'http://foo.com');

        //$body = Stream::factory($this->fakeRawBody);
        $body = json_encode($this->fakeRawBody);
        $response = new Response(200, $this->fakeHeadersAsArray, $body);

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if (['X-foo' => 'bar'] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 123,
                    'connect_timeout' => 10,
                ]);

//                if (count($caInfo) !== 1) {
//                    return false;
//                }
//
//                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
//                    return false;
//                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andReturn($response);

        $response = $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

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
        $request = new Request('GET', 'http://foo.com');

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if ([] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 60,
                    'connect_timeout' => 10,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andThrow(new RequestException('Foo', $request));

        $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
    }
}
