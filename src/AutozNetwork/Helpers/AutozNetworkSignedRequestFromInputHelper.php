<?php

namespace AutozNetwork\Helpers;

use AutozNetwork\Authentication\AccessToken;
use AutozNetwork\Authentication\OAuth2Client;
use AutozNetwork\AutozNetwork;
use AutozNetwork\AutozNetworkApp;
use AutozNetwork\AutozNetworkClient;
use AutozNetwork\SignedRequest;

/**
 * Class AutozNetworkSignedRequestFromInputHelper
 *
 * @package AutozNetwork
 */
abstract class AutozNetworkSignedRequestFromInputHelper
{
    /**
     * @var SignedRequest|null The SignedRequest entity.
     */
    protected $signedRequest;

    /**
     * @var AutozNetworkApp The AutozNetworkApp entity.
     */
    protected $app;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * Initialize the helper and process available signed request data.
     *
     * @param AutozNetworkApp    $app          The AutozNetworkApp entity.
     * @param AutozNetworkClient $client       The client to make HTTP requests.
     * @param string|null    $graphVersion The version of Graph to use.
     */
    public function __construct(AutozNetworkApp $app, AutozNetworkClient $client, $graphVersion = null)
    {
        $this->app = $app;
        $graphVersion = $graphVersion ?: AutozNetwork::DEFAULT_GRAPH_VERSION;
        $this->oAuth2Client = new OAuth2Client($this->app, $client, $graphVersion);

        $this->instantiateSignedRequest();
    }

    /**
     * Instantiates a new SignedRequest entity.
     *
     * @param string|null
     */
    public function instantiateSignedRequest($rawSignedRequest = null)
    {
        $rawSignedRequest = $rawSignedRequest ?: $this->getRawSignedRequest();

        if (! $rawSignedRequest) {
            return;
        }

        $this->signedRequest = new SignedRequest($this->app, $rawSignedRequest);
    }

    /**
     * Returns an AccessToken entity from the signed request.
     *
     * @return AccessToken|null
     *
     * @throws \AutozNetwork\Exceptions\AutozNetworkSDKException
     */
    public function getAccessToken()
    {
        if ($this->signedRequest && $this->signedRequest->hasOAuthData()) {
            $code = $this->signedRequest->get('code');
            $accessToken = $this->signedRequest->get('oauth_token');

            if ($code && ! $accessToken) {
                return $this->oAuth2Client->getAccessTokenFromCode($code);
            }

            $expiresAt = $this->signedRequest->get('expires', 0);

            return new AccessToken($accessToken, $expiresAt);
        }

        return null;
    }

    /**
     * Returns the SignedRequest entity.
     *
     * @return SignedRequest|null
     */
    public function getSignedRequest()
    {
        return $this->signedRequest;
    }

    /**
     * Returns the user_id if available.
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->signedRequest ? $this->signedRequest->getUserId() : null;
    }

    /**
     * Get raw signed request from input.
     *
     * @return string|null
     */
    abstract public function getRawSignedRequest();

    /**
     * Get raw signed request from POST input.
     *
     * @return string|null
     */
    public function getRawSignedRequestFromPost()
    {
        if (isset($_POST['signed_request'])) {
            return $_POST['signed_request'];
        }

        return null;
    }

    /**
     * Get raw signed request from cookie set from the Javascript SDK.
     *
     * @return string|null
     */
    public function getRawSignedRequestFromCookie()
    {
        if (isset($_COOKIE['fbsr_' . $this->app->getId()])) {
            return $_COOKIE['fbsr_' . $this->app->getId()];
        }

        return null;
    }
}
