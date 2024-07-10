<?php
/**
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 *
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
 * List of required attribution notices and acknowledgements for third-party software can be found in the NOTICE file.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Profileo Group - Complete list of authors and contributors to this software can be found in the AUTHORS file.
 * @copyright Since 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace PrestaScan\OAuth2;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Oauth
{
    protected $oAuthEndpointDomain = 'https://security.prestascan.com/';
    protected $apiEndpointDomain = 'https://security.prestascan.com/';

    private $accessToken;
    private $refreshToken;
    private $expires;

    private $accessTokenObj;
    private $providerOauth;
    private $providerCurl;

    public function __construct()
    {
        $this->accessToken = \Configuration::get('PRESTASCAN_ACCESS_TOKEN');
        $this->refreshToken = \Configuration::get('PRESTASCAN_REFRESH_TOKEN');
        $this->expires = (int) \Configuration::get('PRESTASCAN_ACCESS_TOKEN_EXPIRE');

        $devDomainUrl = \Configuration::get('PRESTASCAN_DEV_OAUTH_DOMAIN_URL');
        $devRedirectUrl = \Configuration::get('PRESTASCAN_DEV_OAUTH_REDIRECT_URL');
        if ($devDomainUrl && $devRedirectUrl) {
            // Curstom envrionment for developers
            $this->oAuthEndpointDomain = $devDomainUrl;
            // Such as: http://prestascansecurity_server-laravel.test-1/
            $this->apiEndpointDomain = $devRedirectUrl;
        }

        if (!empty($this->accessToken)) {
            // We already have a token
            $options = array(
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'expires' => $this->expires,
            );
            $this->accessTokenObj = new AccessToken($options);
        }
    }

    public function getProvider($forCurl)
    {
        $this->registerProvider($forCurl);
        return $forCurl ? $this->providerCurl : $this->providerOauth;
    }

    private function registerProvider($forCurl)
    {
        if ($forCurl) {
            if (!empty($this->providerCurl)) {
                return;
            }
        } else {
            if (!empty($this->providerOauth)) {
                return;
            }
        }

        $urlAuthorize = $forCurl ?
            $this->apiEndpointDomain . 'oauth/authorize' :
            $this->oAuthEndpointDomain . 'oauth/authorize';
        $urlAccessToken = $forCurl ?
            $this->apiEndpointDomain . 'oauth/token' :
            $this->oAuthEndpointDomain . 'oauth/token';

        $provider = new Provider([
            'clientId' => (int) \Configuration::get('PRESTASCAN_ACCESS_CLIENT_ID'),
            'clientSecret' => \Configuration::get('PRESTASCAN_ACCESS_CLIENT_SECRET'),
            'redirectUri' => self::getOauth2RedirectUrl(),
            'urlAuthorize' => $urlAuthorize,
            'urlAccessToken' => $urlAccessToken,
            'urlResourceOwnerDetails' => null,
        ]);

        if ($forCurl) {
            $this->providerCurl = $provider;
        } else {
            $this->providerOauth = $provider;
        }
    }

    /*
    * Return an access token (might be expired, so might request for a refresh token)
    */
    public function getAccessTokenObj($refreshIfExpired = true)
    {
        if (empty($this->accessTokenObj)) {
            return false;
        }

        if ($refreshIfExpired && $this->accessTokenObj->hasExpired()) {
            $this->saveTokens($this->refreshToken());
        }

        return $this->accessTokenObj;
    }

    public function saveTokens($accessTokenObj)
    {
        \Configuration::updateGlobalValue('PRESTASCAN_ACCESS_TOKEN', $accessTokenObj->getToken());
        \Configuration::updateGlobalValue('PRESTASCAN_REFRESH_TOKEN', $accessTokenObj->getRefreshToken());
        \Configuration::updateGlobalValue('PRESTASCAN_ACCESS_TOKEN_EXPIRE', $accessTokenObj->getExpires());

        $this->accessTokenObj = $accessTokenObj;
    }

    public static function saveClientCredentials($clientId, $clientSecret)
    {
        \Configuration::updateGlobalValue('PRESTASCAN_ACCESS_CLIENT_ID', $clientId);
        \Configuration::updateGlobalValue('PRESTASCAN_ACCESS_CLIENT_SECRET', $clientSecret);
    }

    public function getNewAccessTokenFromAuthorizationCode($code)
    {
        return $this->getProvider(true)->getAccessToken('authorization_code', ['code' => $code]);
    }

    public function getAuthenticatedRequestWithResponse($method, $endpoint, array $options = [])
    {
        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $this->getProvider(true)->getAuthenticatedRequest(
            $method,
            $this->apiEndpointDomain . $endpoint,
            $this->getAccessTokenObj(),
            $options
        );

        try {
            $response = $this->getProvider(true)->getResponse($request);
        } catch (\PrestaScan\Exception\TooManyAttempsException $exp) {
            // Rate limit
            throw $exp; // Send to controller
        } catch (\Exception $e) {
            // @todo : to test / cases / formatting ?
            throw new \Exception($e);
        }

        $parsed = $this->getProvider(true)->parseResponse($response);
        // Note that "checkResponse" is done later on and should not be done here

        return $parsed;
    }

    public static function getOauth2RedirectUrl()
    {
        $url = \PrestaScan\Tools::getShopUrl();
        // Check if the URL ends with a slash, if not, add a slash
        $url = substr($url, -1) !== '/' ? $url . '/' : $url;
        // Append the query string
        $url .= '?fc=module&module=prestascansecurity&controller=oauth2';
        // Enfore https and return
        return \PrestaScan\Tools::enforeHttpsIfAvailable($url);
    }

    public function getRegistragionUrl()
    {
        return $this->oAuthEndpointDomain . 'generate-user-oauth';
    }

    private function refreshToken()
    {
        return $this->getProvider(true)->getAccessToken(
            'refresh_token',
            ['refresh_token' => $this->accessTokenObj->getRefreshToken()]
        );
    }
}
