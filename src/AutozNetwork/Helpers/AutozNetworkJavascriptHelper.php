<?php

namespace AutozNetwork\Helpers;

/**
 * Class AutozNetworkJavaScriptLoginHelper
 *
 * @package AutozNetwork
 */
class AutozNetworkJavaScriptHelper extends AutozNetworkSignedRequestFromInputHelper
{
    /**
     * Get raw signed request from the cookie.
     *
     * @return string|null
     */
    public function getRawSignedRequest()
    {
        return $this->getRawSignedRequestFromCookie();
    }
}
