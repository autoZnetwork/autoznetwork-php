<?php

namespace AutozNetwork\PersistentData;

use AutozNetwork\Exceptions\AutozNetworkSDKException;

/**
 * Class AutozNetworkSessionPersistentDataHandler
 *
 * @package AutozNetwork
 */
class AutozNetworkSessionPersistentDataHandler implements PersistentDataInterface
{
    /**
     * @var string Prefix to use for session variables.
     */
    protected $sessionPrefix = 'AZN_';

    /**
     * Init the session handler.
     *
     * @param boolean $enableSessionCheck
     *
     * @throws AutozNetworkSDKException
     */
    public function __construct($enableSessionCheck = true)
    {
        if ($enableSessionCheck && session_status() !== PHP_SESSION_ACTIVE) {
            throw new AutozNetworkSDKException(
                'Sessions are not active. Please make sure session_start() is at the top of your script.',
                720
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (isset($_SESSION[$this->sessionPrefix . $key])) {
            return $_SESSION[$this->sessionPrefix . $key];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $_SESSION[$this->sessionPrefix . $key] = $value;
    }
}
