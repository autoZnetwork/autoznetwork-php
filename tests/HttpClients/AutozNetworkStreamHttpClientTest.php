<?php

namespace AutozNetwork\Tests\HttpClients;

use AutozNetwork\HttpClients\AutozNetworkStreamHttpClient;
use Mockery as m;

class AutozNetworkStreamHttpClientTest extends AbstractTestHttpClient
{
    /**
     * @var \AutozNetwork\HttpClients\AutozNetworkStream
     */
    protected $streamMock;

    /**
     * @var AutozNetworkStreamHttpClient
     */
    protected $streamClient;

    protected function setUp(): void
    {
        $this->streamMock = m::mock('AutozNetwork\HttpClients\AutozNetworkStream');
        $this->streamClient = new AutozNetworkStreamHttpClient($this->streamMock);
    }

    public function testCanCompileHeader()
    {
        $headers = [
            'X-foo' => 'bar',
            'X-bar' => 'faz',
        ];
        $header = $this->streamClient->compileHeader($headers);
        $this->assertEquals("X-foo: bar\r\nX-bar: faz", $header);
    }

    public function testCanSendNormalRequest()
    {
        $this->streamMock
            ->shouldReceive('streamContextCreate')
            ->once()
            ->with(m::on(function ($arg) {
                if (! isset($arg['http']) || ! isset($arg['ssl'])) {
                    return false;
                }

                if ($arg['http'] !== [
                        'method' => 'GET',
                        'header' => 'X-foo: bar',
                        'content' => 'foo_body',
                        'timeout' => 123,
                        'ignore_errors' => true,
                    ]
                ) {
                    return false;
                }

                $caInfo = array_diff_assoc($arg['ssl'], [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['cafile'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('getResponseHeaders')
            ->once()
            ->andReturn(explode("\n", trim($this->fakeRawHeader)));
        $this->streamMock
            ->shouldReceive('fileGetContents')
            ->once()
            ->with('http://foo.com/')
            ->andReturn($this->fakeRawBody);

        $response = $this->streamClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

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
        $this->streamMock
            ->shouldReceive('streamContextCreate')
            ->once()
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('getResponseHeaders')
            ->once()
            ->andReturn(null);
        $this->streamMock
            ->shouldReceive('fileGetContents')
            ->once()
            ->with('http://foo.com/')
            ->andReturn(false);

        $this->streamClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
    }
}
