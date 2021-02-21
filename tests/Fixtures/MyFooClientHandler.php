<?php

namespace AutozNetwork\Tests\Fixtures;

use AutozNetwork\Http\GraphRawResponse;
use AutozNetwork\HttpClients\AutozNetworkHttpClientInterface;

class MyFooClientHandler implements AutozNetworkHttpClientInterface
{
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        return new GraphRawResponse(
            "HTTP/1.1 200 OK\r\nDate: Mon, 19 May 2014 18:37:17 GMT",
            '{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}'
        );
    }
}
