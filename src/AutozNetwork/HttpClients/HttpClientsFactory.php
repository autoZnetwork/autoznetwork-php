<?php

namespace AutozNetwork\HttpClients;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;

class HttpClientsFactory
{
    private function __construct()
    {
        // factory constructors are not invoked
    }

    /**
     * HTTP client generation.
     *
     * @param AutozNetworkHttpClientInterface|Client|string|null $handler
     *
     * @throws Exception                If the cURL extension or the Guzzle client aren't available (if required).
     * @throws InvalidArgumentException If the http client handler isn't "curl", "stream", "guzzle", or an instance of AutozNetwork\HttpClients\AutozNetworkHttpClientInterface.
     *
     * @return AutozNetworkHttpClientInterface
     */
    public static function createHttpClient($handler)
    {
        if (! $handler) {
            return self::detectDefaultClient();
        }

        if ($handler instanceof AutozNetworkHttpClientInterface) {
            return $handler;
        }

        if ('stream' === $handler) {
            return new AutozNetworkStreamHttpClient();
        }
        if ('curl' === $handler) {
            if (! extension_loaded('curl')) {
                throw new Exception('The cURL extension must be loaded in order to use the "curl" handler.');
            }

            return new AutozNetworkCurlHttpClient();
        }

        if ('guzzle' === $handler && ! class_exists('GuzzleHttp\Client')) {
            throw new Exception('The Guzzle HTTP client must be included in order to use the "guzzle" handler.');
        }

        if ($handler instanceof Client) {
            return new AutozNetworkGuzzleHttpClient($handler);
        }
        if ('guzzle' === $handler) {
            return new AutozNetworkGuzzleHttpClient();
        }

        throw new InvalidArgumentException('The http client handler must be set to "curl", "stream", "guzzle", be an instance of GuzzleHttp\Client or an instance of AutozNetwork\HttpClients\AutozNetworkHttpClientInterface');
    }

    /**
     * Detect default HTTP client.
     *
     * @return AutozNetworkHttpClientInterface
     */
    private static function detectDefaultClient()
    {
        if (extension_loaded('curl')) {
            return new AutozNetworkCurlHttpClient();
        }

        if (class_exists('GuzzleHttp\Client')) {
            return new AutozNetworkGuzzleHttpClient();
        }

        return new AutozNetworkStreamHttpClient();
    }
}
