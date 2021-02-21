<?php

namespace AutozNetwork\HttpClients;

use AutozNetwork\Exceptions\AutozNetworkSDKException;
use AutozNetwork\Http\GraphRawResponse;

class AutozNetworkStreamHttpClient implements AutozNetworkHttpClientInterface
{
    /**
     * @var AutozNetworkStream Procedural stream wrapper as object.
     */
    protected $autozNetworkStream;

    /**
     * @param autozNetworkStream|null Procedural stream wrapper as object.
     */
    public function __construct(AutozNetworkStream $autozNetworkStream = null)
    {
        $this->autozNetworkStream = $autozNetworkStream ?: new AutozNetworkStream();
    }

    /**
     * @inheritdoc
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => $this->compileHeader($headers),
                'content' => $body,
                'timeout' => $timeOut,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true, // All root certificates are self-signed
                'cafile' => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem',
            ],
        ];

        $this->autozNetworkStream->streamContextCreate($options);
        $rawBody = $this->autozNetworkStream->fileGetContents($url);
        $rawHeaders = $this->autozNetworkStream->getResponseHeaders();

        if ($rawBody === false || empty($rawHeaders)) {
            throw new AutozNetworkSDKException('Stream returned an empty response', 660);
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return new GraphRawResponse($rawHeaders, $rawBody);
    }

    /**
     * Formats the headers for use in the stream wrapper.
     *
     * @param array $headers The request headers.
     *
     * @return string
     */
    public function compileHeader(array $headers)
    {
        $header = [];
        foreach ($headers as $k => $v) {
            $header[] = $k . ': ' . $v;
        }

        return implode("\r\n", $header);
    }
}
