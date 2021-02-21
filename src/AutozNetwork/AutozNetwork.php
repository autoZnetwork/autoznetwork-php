<?php

namespace AutozNetwork;

use AutozNetwork\Authentication\AccessToken;
use AutozNetwork\Authentication\OAuth2Client;
//use AutozNetwork\FileUpload\AutozNetworkFile;
//use AutozNetwork\FileUpload\AutozNetworkResumableUploader;
//use AutozNetwork\FileUpload\AutozNetworkTransferChunk;
//use AutozNetwork\FileUpload\AutozNetworkVideo;
use AutozNetwork\Exceptions\AutozNetworkSDKException;
use AutozNetwork\Helpers\AutozNetworkJavaScriptHelper;
use AutozNetwork\Helpers\AutozNetworkRedirectLoginHelper;
use AutozNetwork\HttpClients\HttpClientsFactory;
use AutozNetwork\PersistentData\PersistentDataFactory;
use AutozNetwork\PersistentData\PersistentDataInterface;
use AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorFactory;
use AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorInterface;
use AutozNetwork\Url\AutozNetworkUrlDetectionHandler;
use AutozNetwork\Url\UrlDetectionInterface;

/**
 * Class AutozNetwork
 *
 * @package AutozNetwork
 */
class AutozNetwork
{
    /**
     * @const string Version number of the AutozNetwork PHP SDK.
     */
    const VERSION = '1.0.0';

    /**
     * @const string Default REST API version for requests.
     */
    const DEFAULT_REST_VERSION = 'v1.0';

    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'AUTOZNETWORK_APP_ID';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'AUTOZNETWORK_APP_SECRET';

    /**
     * @var AutozNetworkApp The AutozNetworkApp entity.
     */
    protected $app;

    /**
     * @var AutozNetworkClient The AutozNetwork client service.
     */
    protected $client;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * @var UrlDetectionInterface|null The URL detection handler.
     */
    protected $urlDetectionHandler;

    /**
     * @var PseudoRandomStringGeneratorInterface|null The cryptographically secure pseudo-random string generator.
     */
    protected $pseudoRandomStringGenerator;

    /**
     * @var AccessToken|null The default access token to use with requests.
     */
    protected $defaultAccessToken;

    /**
     * @var string|null The default Graph version we want to use.
     */
    protected $defaultGraphVersion;

    /**
     * @var PersistentDataInterface|null The persistent data handler.
     */
    protected $persistentDataHandler;

//    /**
//     * @var AutozNetworkResponse|AutozNetworkBatchResponse|null Stores the last request made to Graph.
//     */
//    protected $lastResponse;

    /**
     * Instantiates a new AutozNetwork super-class object.
     *
     * @param array $config
     *
     * @throws AutozNetworkSDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => getenv(static::APP_ID_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'default_rest_version' => static::DEFAULT_REST_VERSION,
            'enable_beta_mode' => false,
            'http_client_handler' => null,
            'persistent_data_handler' => null,
            'pseudo_random_string_generator' => null,
            'url_detection_handler' => null,
        ], $config);

        if (! $config['app_id']) {
            throw new AutozNetworkSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (! $config['app_secret']) {
            throw new AutozNetworkSDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }

        $this->app = new AutozNetworkApp($config['app_id'], $config['app_secret']);
        $this->client = new AutozNetworkClient(
            HttpClientsFactory::createHttpClient($config['http_client_handler']),
            $config['enable_beta_mode']
        );
        $this->pseudoRandomStringGenerator = PseudoRandomStringGeneratorFactory::createPseudoRandomStringGenerator(
            $config['pseudo_random_string_generator']
        );
        $this->setUrlDetectionHandler($config['url_detection_handler'] ?: new AutozNetworkUrlDetectionHandler());
        $this->persistentDataHandler = PersistentDataFactory::createPersistentDataHandler(
            $config['persistent_data_handler']
        );

        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }

//        // Throw an InvalidArgumentException if "default_graph_version" is not set
//        $this->defaultGraphVersion = $config['default_graph_version'];
    }

    /**
     * Returns the AutozNetworkApp entity.
     *
     * @return AutozNetworkApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Returns the AutozNetworkClient service.
     *
     * @return AutozNetworkClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the OAuth 2.0 client service.
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client()
    {
        if (! $this->oAuth2Client instanceof OAuth2Client) {
            $app = $this->getApp();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($app, $client, $this->defaultGraphVersion);
        }

        return $this->oAuth2Client;
    }

//    /**
//     * Returns the last response returned from Graph.
//     *
//     * @return AutozNetworkResponse|AutozNetworkBatchResponse|null
//     */
//    public function getLastResponse()
//    {
//        return $this->lastResponse;
//    }

    /**
     * Returns the URL detection handler.
     *
     * @return UrlDetectionInterface
     */
    public function getUrlDetectionHandler()
    {
        return $this->urlDetectionHandler;
    }

    /**
     * Changes the URL detection handler.
     *
     * @param UrlDetectionInterface $urlDetectionHandler
     */
    private function setUrlDetectionHandler(UrlDetectionInterface $urlDetectionHandler)
    {
        $this->urlDetectionHandler = $urlDetectionHandler;
    }

    /**
     * Returns the default AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param AccessToken|string $accessToken The access token to save.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->defaultAccessToken = new AccessToken($accessToken);

            return;
        }

        if ($accessToken instanceof AccessToken) {
            $this->defaultAccessToken = $accessToken;

            return;
        }

        throw new \InvalidArgumentException('The default access token must be of type "string" or AutozNetwork\AccessToken');
    }

    /**
     * Returns the default Graph version.
     *
     * @return string
     */
    public function getDefaultGraphVersion()
    {
        return $this->defaultGraphVersion;
    }

    /**
     * Returns the redirect login helper.
     *
     * @return AutozNetworkRedirectLoginHelper
     */
    public function getRedirectLoginHelper()
    {
        return new AutozNetworkRedirectLoginHelper(
            $this->getOAuth2Client(),
            $this->persistentDataHandler,
            $this->urlDetectionHandler,
            $this->pseudoRandomStringGenerator
        );
    }

    /**
     * Returns the JavaScript helper.
     *
     * @return AutozNetworkJavaScriptHelper
     */
    public function getJavaScriptHelper()
    {
        return new AutozNetworkJavaScriptHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return AutozNetworkResponse
     *
     * @throws AutozNetworkSDKException
     */
    public function get($endpoint, $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params = [],
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return AutozNetworkResponse
     *
     * @throws AutozNetworkSDKException
     */
    public function post($endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return AutozNetworkResponse
     *
     * @throws AutozNetworkSDKException
     */
    public function delete($endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a request to Graph and returns the result.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return AutozNetworkResponse
     *
     * @throws AutozNetworkSDKException
     */
    public function sendRequest($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag, $graphVersion);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Instantiates a new AutozNetworkRequest entity.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $graphVersion
     *
     * @return AutozNetworkRequest
     *
     * @throws AutozNetworkSDKException
     */
    public function request($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $graphVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        return new AutozNetworkRequest(
            $this->app,
            $accessToken,
            $method,
            $endpoint,
            $params,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Factory to create AutozNetworkFile's.
     *
     * @param string $pathToFile
     *
     * @return AutozNetworkFile
     *
     * @throws AutozNetworkSDKException
     */
    public function fileToUpload($pathToFile)
    {
        return new AutozNetworkFile($pathToFile);
    }
}
