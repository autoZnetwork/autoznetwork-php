<?php

namespace AutozNetwork\Http;

/**
 * Interface
 *
 * @package AutozNetwork
 */
interface RequestBodyInterface
{
    /**
     * Get the body of the request to send to API.
     *
     * @return string
     */
    public function getBody();
}
