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
namespace AutozNetwork\Tests\Helpers;

use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkClient;
use AutozNetwork\Helpers\AutozNetworkJavaScriptHelper;
use PHPUnit\Framework\TestCase;

class AutozNetworkJavaScriptHelperTest extends TestCase
{
    public $rawSignedRequestAuthorized = 'vdZXlVEQ5NTRRTFvJ7Jeo_kP4SKnBDvbNP0fEYKS0Sg=.eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjoxNDAyNTUxMDMxLCJ1c2VyX2lkIjoiMTIzIn0=';

    public function testARawSignedRequestCanBeRetrievedFromCookieData()
    {
        $_COOKIE['fbsr_123'] = $this->rawSignedRequestAuthorized;

        $app = new AutozNetworkApp('123', 'foo_app_secret');
        $helper = new AutozNetworkJavaScriptHelper($app, new AutozNetworkClient());

        $rawSignedRequest = $helper->getRawSignedRequest();

        $this->assertEquals($this->rawSignedRequestAuthorized, $rawSignedRequest);
    }
}
