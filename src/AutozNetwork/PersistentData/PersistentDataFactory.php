<?php

namespace AutozNetwork\PersistentData;

use InvalidArgumentException;

class PersistentDataFactory
{
    private function __construct()
    {
        // a factory constructor should never be invoked
    }

    /**
     * PersistentData generation.
     *
     * @param PersistentDataInterface|string|null $handler
     *
     * @throws InvalidArgumentException If the persistent data handler isn't "session", "memory", or an instance of AutozNetwork\PersistentData\PersistentDataInterface.
     *
     * @return PersistentDataInterface
     */
    public static function createPersistentDataHandler($handler)
    {
        if (!$handler) {
            return session_status() === PHP_SESSION_ACTIVE
                ? new AutozNetworkSessionPersistentDataHandler()
                : new AutozNetworkMemoryPersistentDataHandler();
        }

        if ($handler instanceof PersistentDataInterface) {
            return $handler;
        }

        if ('session' === $handler) {
            return new AutozNetworkSessionPersistentDataHandler();
        }
        if ('memory' === $handler) {
            return new AutozNetworkMemoryPersistentDataHandler();
        }

        throw new InvalidArgumentException('The persistent data handler must be set to "session", "memory", or be an instance of AutozNetwork\PersistentData\PersistentDataInterface');
    }
}
