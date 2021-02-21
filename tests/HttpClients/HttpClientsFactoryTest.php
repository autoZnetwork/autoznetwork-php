<?php

namespace AutozNetwork\Tests\HttpClients;

use AutozNetwork\HttpClients\AutozNetworkCurlHttpClient;
use AutozNetwork\HttpClients\AutozNetworkGuzzleHttpClient;
use AutozNetwork\HttpClients\AutozNetworkStreamHttpClient;
use AutozNetwork\HttpClients\HttpClientsFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class HttpClientsFactoryTest extends TestCase
{
    const COMMON_NAMESPACE = 'AutozNetwork\HttpClients\\';
    const COMMON_INTERFACE = 'AutozNetwork\HttpClients\AutozNetworkHttpClientInterface';

    /**
     * @param mixed  $handler
     * @param string $expected
     *
     * @dataProvider httpClientsProvider
     */
    public function testCreateHttpClient($handler, $expected)
    {
        $httpClient = HttpClientsFactory::createHttpClient($handler);

        $this->assertInstanceOf(self::COMMON_INTERFACE, $httpClient);
        $this->assertInstanceOf($expected, $httpClient);
    }

    /**
     * @return array
     */
    public function httpClientsProvider()
    {
        $clients = [
            ['guzzle', self::COMMON_NAMESPACE . 'AutozNetworkGuzzleHttpClient'],
            ['stream', self::COMMON_NAMESPACE . 'AutozNetworkStreamHttpClient'],
            [new Client(), self::COMMON_NAMESPACE . 'AutozNetworkGuzzleHttpClient'],
            [new AutozNetworkGuzzleHttpClient(), self::COMMON_NAMESPACE . 'AutozNetworkGuzzleHttpClient'],
            [new AutozNetworkStreamHttpClient(), self::COMMON_NAMESPACE . 'AutozNetworkStreamHttpClient'],
            [null, self::COMMON_INTERFACE],
        ];
        if (extension_loaded('curl')) {
            $clients[] = ['curl', self::COMMON_NAMESPACE . 'AutozNetworkCurlHttpClient'];
            $clients[] = [new AutozNetworkCurlHttpClient(), self::COMMON_NAMESPACE . 'AutozNetworkCurlHttpClient'];
        }

        return $clients;
    }
}
