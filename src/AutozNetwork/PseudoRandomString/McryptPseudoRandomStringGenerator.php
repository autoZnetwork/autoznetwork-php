<?php
/**
 * Copyright 2017 AutozNetwork, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * AutozNetwork.
 *
 * As with any software that integrates with the AutozNetwork platform, your use
 * of this software is subject to the AutozNetwork Developer Principles and
 * Policies [http://developers.AutozNetwork.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace AutozNetwork\PseudoRandomString;

use AutozNetwork\Exceptions\AutozNetworkSDKException;

class McryptPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from mcrypt_create_iv(). ';

    /**
     * @throws AutozNetworkSDKException
     */
    public function __construct()
    {
        if (! function_exists('mcrypt_create_iv')) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'The function mcrypt_create_iv() does not exist.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        $binaryString = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);

        if ($binaryString === false) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'mcrypt_create_iv() returned an error.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
