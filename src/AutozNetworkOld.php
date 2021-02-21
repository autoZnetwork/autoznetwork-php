<?php

namespace AutozNetwork;

use AutozNetwork\Rest\Feed;
use AutozNetwork\Rest\Inventory;
use AutozNetwork\Rest\Location;
use AutozNetwork\Rest\Organization;
use AutozNetwork\Rest\Product;
use AutozNetwork\Rest\User;
use InvalidArgumentException;

class AutozNetworkOld
{
    private $version;
    private string $apiKey;
    private string $clientVersion;

    public function __construct($apiKey = null, $version = null)
    {
        if (! is_null($apiKey)) {
            $this->setApiKey($apiKey);
        }
        $this->version = $version;
        $this->clientVersion = '1.0.0';
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        if (! is_string($apiKey) || empty($apiKey)) {
            throw new InvalidArgumentException('API Key must be a non-empty string.');
        }
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getClientVersion()
    {
        return $this->clientVersion;
    }

    public function location()
    {
        return new Location($this);
    }

    public function feed()
    {
        return new Feed($this);
    }

    public function inventory()
    {
        return new Inventory($this);
    }

    public function organization()
    {
        return new Organization($this);
    }

    public function product()
    {
        return new Product($this);
    }

    public function user()
    {
        return new User($this);
    }
}
