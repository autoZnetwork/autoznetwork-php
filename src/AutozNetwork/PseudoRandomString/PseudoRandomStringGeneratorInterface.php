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

/**
 * Interface
 *
 * @package AutozNetwork
 */
interface PseudoRandomStringGeneratorInterface
{
    /**
     * Get a cryptographically secure pseudo-random string of arbitrary length.
     *
     * @see http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
     *
     * @param int $length The length of the string to return.
     *
     * @return string
     *
     * @throws \AutozNetwork\Exceptions\AutozNetworkSDKException|\InvalidArgumentException
     */
    public function getPseudoRandomString($length);
}
