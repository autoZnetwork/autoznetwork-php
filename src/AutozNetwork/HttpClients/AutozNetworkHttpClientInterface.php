<?php

namespace AutozNetwork\HttpClients;

/**
 * Interface AutozNetworkHttpClientInterface
 *
 * @package AutozNetwork
 */
interface AutozNetworkHttpClientInterface
{
    /**
     * Sends a request to the server and returns the raw response.
     *
     * @param string $url     The endpoint to send the request to.
     * @param string $method  The request method.
     * @param string $body    The body of the request.
     * @param array  $headers The request headers.
     * @param int    $timeOut The timeout in seconds for the request.
     *
     * @return \AutozNetwork\Http\GraphRawResponse Raw response from the server.
     *
     * @throws \AutozNetwork\Exceptions\AutozNetworkSDKException
     */
    public function send($url, $method, $body, array $headers, $timeOut);
}
