<?php

namespace AutozNetwork\Tests\Http;

use AutozNetwork\Http\RequestBodyMultipart;
use AutozNetwork\FileUpload\AutozNetworkFile;
use PHPUnit\Framework\TestCase;


class RequestBodyMultipartTest extends TestCase
{
    public function testCanProperlyEncodeAnArrayOfParams()
    {
        $message = new RequestBodyMultipart([
            'foo' => 'bar',
            'scawy_vawues' => '@FooBar is a real twitter handle.',
        ], [], 'foo_boundary');
        $body = $message->getBody();

        $expectedBody = "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"scawy_vawues\"\r\n\r\n@FooBar is a real twitter handle.\r\n";
        $expectedBody .= "--foo_boundary--\r\n";

        $this->assertEquals($expectedBody, $body);
    }

    public function testCanProperlyEncodeFilesAndParams()
    {
        $file = new AutozNetworkFile(__DIR__ . '/../foo.txt');
        $message = new RequestBodyMultipart([
            'foo' => 'bar',
        ], [
            'foo_file' => $file,
        ], 'foo_boundary');
        $body = $message->getBody();

        $expectedBody = "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo_file\"; filename=\"foo.txt\"\r\n";
        $expectedBody .= "Content-Type: text/plain\r\n\r\nThis is a text file used for testing. Let's dance.\r\n";
        $expectedBody .= "--foo_boundary--\r\n";

        $this->assertEquals($expectedBody, $body);
    }

    public function testSupportsMultidimensionalParams()
    {
        $message = new RequestBodyMultipart([
            'foo' => 'bar',
            'faz' => [1,2,3],
            'targeting' => [
                'countries' => 'US,GB',
                'age_min' => 13,
            ],
            'call_to_action' => [
                'type' => 'LEARN_MORE',
                'value' => [
                    'link' => 'http://example.com',
                    'sponsorship' => [
                        'image' => 'http://example.com/bar.jpg',
                    ],
                ],
            ],
        ], [], 'foo_boundary');
        $body = $message->getBody();

        $expectedBody = "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"faz[0]\"\r\n\r\n1\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"faz[1]\"\r\n\r\n2\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"faz[2]\"\r\n\r\n3\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"targeting[countries]\"\r\n\r\nUS,GB\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"targeting[age_min]\"\r\n\r\n13\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"call_to_action[type]\"\r\n\r\nLEARN_MORE\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"call_to_action[value][link]\"\r\n\r\nhttp://example.com\r\n";
        $expectedBody .= "--foo_boundary\r\n";
        $expectedBody .= "Content-Disposition: form-data; name=\"call_to_action[value][sponsorship][image]\"\r\n\r\nhttp://example.com/bar.jpg\r\n";
        $expectedBody .= "--foo_boundary--\r\n";

        $this->assertEquals($expectedBody, $body);
    }
}
