<?php

namespace AutozNetwork\Tests;

use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkRequest;
use AutozNetwork\AutozNetworkResponse;
use PHPUnit\Framework\TestCase;

class AutozNetworkResponseTest extends TestCase
{
    /**
     * @var \AutozNetwork\AutozNetworkRequest
     */
    protected $request;

    protected function setUp(): void
    {
        $app = new AutozNetworkApp('123', 'foo_secret');
        $this->request = new AutozNetworkRequest(
            $app,
            'foo_token',
            'GET',
            '/me/photos?keep=me',
            ['foo' => 'bar'],
            'foo_eTag',
            'v1337'
        );
    }

    public function testAnETagCanBeProperlyAccessed()
    {
        $response = new AutozNetworkResponse($this->request, '', 200, ['ETag' => 'foo_tag']);

        $eTag = $response->getETag();

        $this->assertEquals('foo_tag', $eTag);
    }

    public function testAProperAppSecretProofCanBeGenerated()
    {
        $response = new AutozNetworkResponse($this->request);

        $appSecretProof = $response->getAppSecretProof();

        $this->assertEquals('df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9', $appSecretProof);
    }

//    public function testASuccessfulJsonResponseWillBeDecodedToAGraphNode()
//    {
//        $graphResponseJson = '{"id":"123","name":"Foo"}';
//        $response = new AutozNetworkResponse($this->request, $graphResponseJson, 200);
//
//        $decodedResponse = $response->getDecodedBody();
//        $graphNode = $response->getGraphNode();
//
//        $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
//        $this->assertEquals([
//            'id' => '123',
//            'name' => 'Foo',
//        ], $decodedResponse);
//        $this->assertInstanceOf('AutozNetwork\GraphNodes\GraphNode', $graphNode);
//    }
//
//    public function testASuccessfulJsonResponseWillBeDecodedToAGraphEdge()
//    {
//        $graphResponseJson = '{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}';
//        $response = new AutozNetworkResponse($this->request, $graphResponseJson, 200);
//
//        $graphEdge = $response->getGraphEdge();
//
//        $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
//        $this->assertInstanceOf('AutozNetwork\GraphNodes\GraphNode', $graphEdge[0]);
//        $this->assertInstanceOf('AutozNetwork\GraphNodes\GraphNode', $graphEdge[1]);
//    }

    public function testASuccessfulUrlEncodedKeyValuePairResponseWillBeDecoded()
    {
        $graphResponseKeyValuePairs = 'id=123&name=Foo';
        $response = new AutozNetworkResponse($this->request, $graphResponseKeyValuePairs, 200);

        $decodedResponse = $response->getDecodedBody();

        $this->assertFalse($response->isError(), 'Did not expect Response to return an error.');
        $this->assertEquals([
            'id' => '123',
            'name' => 'Foo',
        ], $decodedResponse);
    }

    public function testErrorStatusCanBeCheckedWhenAnErrorResponseIsReturned()
    {
        $graphResponse = '{"error":{"message":"Foo error.","type":"OAuthException","code":190,"error_subcode":463}}';
        $response = new AutozNetworkResponse($this->request, $graphResponse, 401);

        $exception = $response->getThrownException();

        $this->assertTrue($response->isError(), 'Expected Response to return an error.');
        $this->assertInstanceOf('AutozNetwork\Exceptions\AutozNetworkResponseException', $exception);
    }
}
