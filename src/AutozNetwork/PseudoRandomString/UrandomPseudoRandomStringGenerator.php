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

class UrandomPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{

    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from /dev/urandom. ';

    /**
     * @throws AutozNetworkSDKException
     */
    public function __construct()
    {
        if (ini_get('open_basedir')) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'There is an open_basedir constraint that prevents access to /dev/urandom.'
            );
        }

        if (!is_readable('/dev/urandom')) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'Unable to read from /dev/urandom.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        $stream = fopen('/dev/urandom', 'rb');
        if (!is_resource($stream)) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'Unable to open stream to /dev/urandom.'
            );
        }

        if (!defined('HHVM_VERSION')) {
            stream_set_read_buffer($stream, 0);
        }

        $binaryString = fread($stream, $length);
        fclose($stream);

        if (!$binaryString) {
            throw new AutozNetworkSDKException(
                static::ERROR_MESSAGE .
                'Stream to /dev/urandom returned no data.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
