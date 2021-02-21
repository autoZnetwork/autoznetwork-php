<?php

namespace AutozNetwork\PseudoRandomString;

use AutozNetwork\Exceptions\AutozNetworkSDKException;

class RandomBytesPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from random_bytes(). ';

    /**
     * @throws AutozNetworkSDKException
     */
    public function __construct()
    {
        if (! function_exists('random_bytes')) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'The function random_bytes() does not exist.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        return $this->binToHex(random_bytes($length), $length);
    }
}
