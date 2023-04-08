<?php
/*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <maxime.morel@profileo.com>
 * 
 * Complete list of authors and contributors to this software can be found in the AUTHORS file.
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
 */

namespace PrestaScan\OAuth2;

class Provider {

    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $urlAuthorize;
    protected $urlAccessToken;
    protected $urlResourceOwnerDetails;

    public function __construct($options)
    {
        $this->clientId = isset($options['clientId']) ? $options['clientId'] : null;
        $this->clientSecret = isset($options['clientSecret']) ? $options['clientSecret'] : null;
        $this->redirectUri = isset($options['redirectUri']) ? $options['redirectUri'] : null;
        $this->urlAuthorize = isset($options['urlAuthorize']) ? $options['urlAuthorize'] : null;
        $this->urlAccessToken = isset($options['urlAccessToken']) ? $options['urlAccessToken'] : null;
        $this->urlResourceOwnerDetails = isset($options['urlResourceOwnerDetails']) ? $options['urlResourceOwnerDetails'] : null;
    }

    public function getAuthenticatedRequest($method, $url, $accessToken, array $options = [])
    {
        $headers = isset($options['headers']) ? $options['headers'] : [];
        $headers[] = 'Authorization: Bearer ' . $accessToken->getToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if (isset($options['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        return $ch;
    }

    public function getAuthorizationUrl($options = [])
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ];

        if (isset($options['scope'])) {
            $params['scope'] = is_array($options['scope']) ? implode(' ', $options['scope']) : $options['scope'];
        }

        if (isset($options['state'])) {
            $params['state'] = $options['state'];
        } else {
            throw new \Exception("`state` must be defined");
        }

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return $this->urlAuthorize . '?' . $queryString;
    }

    public function getAccessToken($grantType, array $options = [])
    {
        $params = [
            'grant_type' => $grantType,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        if ($grantType === 'authorization_code') {
            if (!isset($options['code'])) {
                throw new \InvalidArgumentException('Missing required "code" parameter.');
            }
            $params['code'] = $options['code'];
        } elseif ($grantType === 'refresh_token') {
            if (!isset($options['refresh_token'])) {
                throw new \InvalidArgumentException('Missing required "refresh_token" parameter.');
            }
            $params['refresh_token'] = $options['refresh_token'];
        } else {
            throw new \InvalidArgumentException('Invalid grant_type.');
        }

        $headers = [
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->urlAccessToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = $this->getResponse($ch);
        $parsedResponse = $this->parseResponse($response);

        if (isset($parsedResponse['error'])) {
            throw new \Exception("Error in OAuth2 response: " . $parsedResponse['error'] . " - " . $parsedResponse['error_description']);
        }

        // Convert the "expires_in" field to an absolute timestamp.
        if (isset($parsedResponse['expires_in'])) {
            $parsedResponse['expires'] = time() + $parsedResponse['expires_in'];
        }

        return new AccessToken($parsedResponse);
    }

    public function getState($length = 32)
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length / 2));
        } elseif (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length / 2, MCRYPT_DEV_URANDOM));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length / 2));
        }

        throw new \Exception("No suitable random generator found");
    }

    public function getResponse($ch)
    {
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            //echo 'Curl error: ' . curl_error($ch);
            if (curl_errno() === 28) {
                // timeout
                throw new \Exception("Request timeout. It appears that our server is currently not reachable. This may be due to an unusually high volume of demand. Please try again later");
            } else {
                throw new \Exception("Request error: ".curl_error($ch));
            }
        }

        curl_close($ch);

        if ($httpCode === 403) {
            $response = $this->parseResponse($response);
            if (isset($response['error'])
                && isset($response['error']['message'])
                && $response['error']['message'] === "Too Many Attempts.") {
                throw new \PrestaScan\Exception\TooManyAttempsException("Request limit reached.");
            }
        } else if ($httpCode >= 400) {
            throw new \Exception("HTTP error: " . $httpCode . " - " . $response);
        }

        return $response;
    }

    public function parseResponse($response)
    {
        return json_decode($response, true);
    }

}
