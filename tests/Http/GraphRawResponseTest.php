<?php

namespace AutozNetwork\Tests\Http;

use AutozNetwork\Http\GraphRawResponse;
use PHPUnit\Framework\TestCase;

class GraphRawResponseTest extends TestCase
{

    protected $fakeRawProxyHeader = "HTTP/1.0 200 Connection established
Proxy-agent: Kerio Control/7.1.1 build 1971\r\n\r\n";
    protected $fakeRawHeader = <<<HEADER
HTTP/1.1 200 OK
Etag: "9d86b21aa74d74e574bbb35ba13524a52deb96e3"
Content-Type: text/javascript; charset=UTF-8
X-FB-Rev: 9244768
Date: Mon, 19 May 2014 18:37:17 GMT
X-FB-Debug: 02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=
Access-Control-Allow-Origin: *\r\n\r\n
HEADER;
    protected $fakeHeadersAsArray = [
        'Etag' => '"9d86b21aa74d74e574bbb35ba13524a52deb96e3"',
        'Content-Type' => 'text/javascript; charset=UTF-8',
        'X-FB-Rev' => '9244768',
        'Date' => 'Mon, 19 May 2014 18:37:17 GMT',
        'X-FB-Debug' => '02QQiffE7JG2rV6i/Agzd0gI2/OOQ2lk5UW0=',
        'Access-Control-Allow-Origin' => '*',
    ];

    protected $jsonFakeHeader = 'x-fb-ads-insights-throttle: {"app_id_util_pct": 0.00,"acc_id_util_pct": 0.00}';
    protected $jsonFakeHeaderAsArray = ['x-fb-ads-insights-throttle' => '{"app_id_util_pct": 0.00,"acc_id_util_pct": 0.00}'];

    public function testCanSetTheHeadersFromAnArray()
    {
        $myHeaders = [
            'foo' => 'bar',
            'baz' => 'faz',
        ];
        $response = new GraphRawResponse($myHeaders, '');
        $headers = $response->getHeaders();

        $this->assertEquals($myHeaders, $headers);
    }

    public function testCanSetTheHeadersFromAString()
    {
        $response = new GraphRawResponse($this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        $this->assertEquals($this->fakeHeadersAsArray, $headers);
        $this->assertEquals(200, $httpResponseCode);
    }

    public function testWillIgnoreProxyHeaders()
    {
        $response = new GraphRawResponse($this->fakeRawProxyHeader . $this->fakeRawHeader, '');
        $headers = $response->getHeaders();
        $httpResponseCode = $response->getHttpResponseCode();

        $this->assertEquals($this->fakeHeadersAsArray, $headers);
        $this->assertEquals(200, $httpResponseCode);
    }

    public function testCanTransformJsonHeaderValues()
    {
        $response = new GraphRawResponse($this->jsonFakeHeader, '');
        $headers = $response->getHeaders();

        $this->assertEquals($this->jsonFakeHeaderAsArray['x-fb-ads-insights-throttle'], $headers['x-fb-ads-insights-throttle']);
    }

    public function testHttpResponseCode()
    {
        // HTTP/1.0
        $headers = str_replace('HTTP/1.1', 'HTTP/1.0', $this->fakeRawHeader);
        $response = new GraphRawResponse($headers, '');
        $this->assertEquals(200, $response->getHttpResponseCode());

        // HTTP/1.1
        $response = new GraphRawResponse($this->fakeRawHeader, '');
        $this->assertEquals(200, $response->getHttpResponseCode());

        // HTTP/2
        $headers = str_replace('HTTP/1.1', 'HTTP/2', $this->fakeRawHeader);
        $response = new GraphRawResponse($headers, '');
        $this->assertEquals(200, $response->getHttpResponseCode());
    }
}
