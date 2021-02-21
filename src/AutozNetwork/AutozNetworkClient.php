<?php

namespace AutozNetwork;

use AutozNetwork\Exceptions\AutozNetworkSDKException;
use AutozNetwork\HttpClients\AutozNetworkCurlHttpClient;
use AutozNetwork\HttpClients\AutozNetworkHttpClientInterface;
use AutozNetwork\HttpClients\AutozNetworkStreamHttpClient;

/**
 * Class AutozNetworkClient
 *
 * @package AutozNetwork
 */
class AutozNetworkClient
{
    /**
     * @const string Production REST API URL.
     */
    const BASE_REST_API_URL = 'http://api.autoznetwork.com';

    /**
     * @const string Beta REST API URL.
     */
    const BASE_REST_API_URL_BETA = 'http://api.beta.autoznetwork.com';

    /**
     * @const int The timeout in seconds for a normal request.
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * @const int The timeout in seconds for a request that contains file uploads.
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * @const int The timeout in seconds for a request that contains video uploads.
     */
    const DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT = 7200;

    /**
     * @var bool Toggle to use Graph beta url.
     */
    protected $enableBetaMode = false;

    /**
     * @var AutozNetworkHttpClientInterface HTTP client handler.
     */
    protected $httpClientHandler;

    /**
     * @var int The number of calls that have been made to Graph.
     */
    public static $requestCount = 0;

    /**
     * Instantiates a new AutozNetworkClient object.
     *
     * @param AutozNetworkHttpClientInterface|null $httpClientHandler
     * @param bool                          $enableBeta
     */
    public function __construct(AutozNetworkHttpClientInterface $httpClientHandler = null, $enableBeta = false)
    {
        $this->httpClientHandler = $httpClientHandler ?: $this->detectHttpClientHandler();
        $this->enableBetaMode = $enableBeta;
    }

    /**
     * Sets the HTTP client handler.
     *
     * @param AutozNetworkHttpClientInterface $httpClientHandler
     */
    public function setHttpClientHandler(AutozNetworkHttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;
    }

    /**
     * Returns the HTTP client handler.
     *
     * @return AutozNetworkHttpClientInterface
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * Detects which HTTP client handler to use.
     *
     * @return AutozNetworkHttpClientInterface
     */
    public function detectHttpClientHandler()
    {
        return extension_loaded('curl') ? new AutozNetworkCurlHttpClient() : new AutozNetworkStreamHttpClient();
    }

    /**
     * Toggle beta mode.
     *
     * @param bool $betaMode
     */
    public function enableBetaMode($betaMode = true)
    {
        $this->enableBetaMode = $betaMode;
    }

    /**
     * Returns the base REST URL.
     *
     * @return string
     */
    public function getBaseRestUrl()
    {
        return $this->enableBetaMode ? static::BASE_REST_API_URL_BETA : static::BASE_REST_API_URL;
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     * @param AutozNetworkRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(AutozNetworkRequest $request)
    {
        $url = $this->getBaseRestUrl() . $request->getUrl();

        // If we're sending files they should be sent as multipart/form-data
        if ($request->containsFileUploads()) {
            $requestBody = $request->getMultipartBody();
            $request->setHeaders([
                'Content-Type' => 'multipart/form-data; boundary=' . $requestBody->getBoundary(),
            ]);
        } else {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
        }

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody(),
        ];
    }

    /**
     * Makes the request to Graph and returns the result.
     *
     * @param AutozNetworkRequest $request
     *
     * @return AutozNetworkResponse
     *
     * @throws AutozNetworkSDKException
     */
    public function sendRequest(AutozNetworkRequest $request)
    {
        if (get_class($request) === 'AutozNetwork\AutozNetworkRequest') {
            $request->validateAccessToken();
        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);

        // Since file uploads can take a while, we need to give more time for uploads
        $timeOut = static::DEFAULT_REQUEST_TIMEOUT;
        if ($request->containsFileUploads()) {
            $timeOut = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        } elseif ($request->containsVideoUploads()) {
            $timeOut = static::DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT;
        }

        // Should throw `AutozNetworkSDKException` exception on HTTP client error.
        // Don't catch to allow it to bubble up.
        $rawResponse = $this->httpClientHandler->send($url, $method, $body, $headers, $timeOut);

        static::$requestCount++;

        $returnResponse = new AutozNetworkResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getHttpResponseCode(),
            $rawResponse->getHeaders()
        );

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }
}
