<?php

namespace AutozNetwork\Url;

/**
 * Interface UrlDetectionInterface
 *
 * @package AutozNetwork
 */
interface UrlDetectionInterface
{
    /**
     * Get the currently active URL.
     *
     * @return string
     */
    public function getCurrentUrl();
}
