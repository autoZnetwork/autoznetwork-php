<?php

namespace AutozNetwork\Tests\PersistentData;

use AutozNetwork\PersistentData\AutozNetworkMemoryPersistentDataHandler;
use AutozNetwork\PersistentData\AutozNetworkSessionPersistentDataHandler;
use AutozNetwork\PersistentData\PersistentDataFactory;
use PHPUnit\Framework\TestCase;

class PersistentDataFactoryTest extends TestCase
{
    const COMMON_NAMESPACE = 'AutozNetwork\PersistentData\\';
    const COMMON_INTERFACE = 'AutozNetwork\PersistentData\PersistentDataInterface';

    /**
     * @param mixed  $handler
     * @param string $expected
     *
     * @dataProvider persistentDataHandlerProviders
     */
    public function testCreatePersistentDataHandler($handler, $expected)
    {
        $persistentDataHandler = PersistentDataFactory::createPersistentDataHandler($handler);

        $this->assertInstanceOf(self::COMMON_INTERFACE, $persistentDataHandler);
        $this->assertInstanceOf($expected, $persistentDataHandler);
    }

    /**
     * @return array
     */
    public function persistentDataHandlerProviders()
    {
        $handlers = [
            ['memory', self::COMMON_NAMESPACE . 'AutozNetworkMemoryPersistentDataHandler'],
            [new AutozNetworkMemoryPersistentDataHandler(), self::COMMON_NAMESPACE . 'AutozNetworkMemoryPersistentDataHandler'],
            [new AutozNetworkSessionPersistentDataHandler(false), self::COMMON_NAMESPACE . 'AutozNetworkSessionPersistentDataHandler'],
            [null, self::COMMON_INTERFACE],
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $handlers[] = ['session', self::COMMON_NAMESPACE . 'AutozNetworkSessionPersistentDataHandler'];
        }

        return $handlers;
    }
}
