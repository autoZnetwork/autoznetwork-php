<?php

namespace AutozNetwork\Helpers;

use AutozNetwork\Authentication\AccessToken;
use AutozNetwork\Authentication\OAuth2Client;
use AutozNetwork\Exceptions\AutozNetworkSDKException;
use AutozNetwork\PersistentData\AutozNetworkSessionPersistentDataHandler;
use AutozNetwork\PersistentData\PersistentDataInterface;
use AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorFactory;
use AutozNetwork\PseudoRandomString\PseudoRandomStringGeneratorInterface;
use AutozNetwork\Url\AutozNetworkUrlDetectionHandler;
use AutozNetwork\Url\AutozNetworkUrlManipulator;
use AutozNetwork\Url\UrlDetectionInterface;

/**
 * Class AutozNetworkRedirectLoginHelper
 *
 * @package AutozNetwork
 */
class AutozNetworkRedirectLoginHelper
{
    /**
     * @const int The length of CSRF string to validate the login link.
     */
    const CSRF_LENGTH = 32;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * @var UrlDetectionInterface The URL detection handler.
     */
    protected $urlDetectionHandler;

    /**
     * @var PersistentDataInterface The persistent data handler.
     */
    protected $persistentDataHandler;

    /**
     * @var PseudoRandomStringGeneratorInterface The cryptographically secure pseudo-random string generator.
     */
    protected $pseudoRandomStringGenerator;

    /**
     * @param OAuth2Client                              $oAuth2Client          The OAuth 2.0 client service.
     * @param PersistentDataInterface|null              $persistentDataHandler The persistent data handler.
     * @param UrlDetectionInterface|null                $urlHandler            The URL detection handler.
     * @param PseudoRandomStringGeneratorInterface|null $prsg                  The cryptographically secure pseudo-random string generator.
     */
    public function __construct(OAuth2Client $oAuth2Client, PersistentDataInterface $persistentDataHandler = null, UrlDetectionInterface $urlHandler = null, PseudoRandomStringGeneratorInterface $prsg = null)
    {
        $this->oAuth2Client = $oAuth2Client;
        $this->persistentDataHandler = $persistentDataHandler ?: new AutozNetworkSessionPersistentDataHandler();
        $this->urlDetectionHandler = $urlHandler ?: new AutozNetworkUrlDetectionHandler();
        $this->pseudoRandomStringGenerator = PseudoRandomStringGeneratorFactory::createPseudoRandomStringGenerator($prsg);
    }

    /**
     * Returns the persistent data handler.
     *
     * @return PersistentDataInterface
     */
    public function getPersistentDataHandler()
    {
        return $this->persistentDataHandler;
    }

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
     * Returns the cryptographically secure pseudo-random string generator.
     *
     * @return PseudoRandomStringGeneratorInterface
     */
    public function getPseudoRandomStringGenerator()
    {
        return $this->pseudoRandomStringGenerator;
    }

    /**
     * Stores CSRF state and returns a URL to which the user should be sent to in order to continue the login process with AutozNetwork.
     *
     * @param string $redirectUrl The URL AutozNetwork should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param array  $params      An array of parameters to generate URL.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
    {
        $state = $this->persistentDataHandler->get('state') ?: $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);
        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to AutozNetwork.
     *
     * @param string $redirectUrl The URL AutozNetwork should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getLoginUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Returns the URL to send the user in order to log out of AutozNetwork.
     *
     * @param AccessToken|string $accessToken The access token that will be logged out.
     * @param string             $next        The url AutozNetwork should redirect the user to after a successful logout.
     * @param string             $separator   The separator to use in http_build_query().
     *
     * @return string
     *
     * @throws AutozNetworkSDKException
     */
    public function getLogoutUrl($accessToken, $next, $separator = '&')
    {
        if (! $accessToken instanceof AccessToken) {
            $accessToken = new AccessToken($accessToken);
        }

        if ($accessToken->isAppAccessToken()) {
            throw new AutozNetworkSDKException('Cannot generate a logout URL with an app access token.', 722);
        }

        $params = [
            'next' => $next,
            'access_token' => $accessToken->getValue(),
        ];

        return 'https://www.AutozNetwork.com/logout.php?' . http_build_query($params, null, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to AutozNetwork with permission(s) to be re-asked.
     *
     * @param string $redirectUrl The URL AutozNetwork should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReRequestUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'rerequest'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to AutozNetwork with user to be re-authenticated.
     *
     * @param string $redirectUrl The URL AutozNetwork should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReAuthenticationUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'reauthenticate'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @param string|null $redirectUrl The redirect URL.
     *
     * @return AccessToken|null
     *
     * @throws AutozNetworkSDKException
     */
    public function getAccessToken($redirectUrl = null)
    {
        if (! $code = $this->getCode()) {
            return null;
        }

        $this->validateCsrf();
        $this->resetCsrf();

        $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
        // At minimum we need to remove the 'code', 'enforce_https' and 'state' params
        $redirectUrl = AutozNetworkUrlManipulator::removeParamsFromUrl($redirectUrl, ['code', 'enforce_https', 'state']);

        return $this->oAuth2Client->getAccessTokenFromCode($code, $redirectUrl);
    }

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws AutozNetworkSDKException
     */
    protected function validateCsrf()
    {
        $state = $this->getState();
        if (! $state) {
            throw new AutozNetworkSDKException('Cross-site request forgery validation failed. Required GET param "state" missing.');
        }
        $savedState = $this->persistentDataHandler->get('state');
        if (! $savedState) {
            throw new AutozNetworkSDKException('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
        }

        if (\hash_equals($savedState, $state)) {
            return;
        }

        throw new AutozNetworkSDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf()
    {
        $this->persistentDataHandler->set('state', null);
    }

    /**
     * Return the code.
     *
     * @return string|null
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * Return the state.
     *
     * @return string|null
     */
    protected function getState()
    {
        return $this->getInput('state');
    }

    /**
     * Return the error code.
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->getInput('error_code');
    }

    /**
     * Returns the error.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->getInput('error');
    }

    /**
     * Returns the error reason.
     *
     * @return string|null
     */
    public function getErrorReason()
    {
        return $this->getInput('error_reason');
    }

    /**
     * Returns the error description.
     *
     * @return string|null
     */
    public function getErrorDescription()
    {
        return $this->getInput('error_description');
    }

    /**
     * Returns a value from a GET param.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getInput($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
}
