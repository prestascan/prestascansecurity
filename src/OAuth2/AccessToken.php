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

class AccessToken
{
    private $accessToken;
    private $expires;
    private $refreshToken;
    private $scope;

    public function __construct(array $options)
    {
        $this->accessToken = $options['access_token'];
        $this->expires = isset($options['expires']) ? $options['expires'] : null;
        $this->refreshToken = isset($options['refresh_token']) ? $options['refresh_token'] : null;
        $this->scope = isset($options['scope']) ? $options['scope'] : null;
    }

    public function getToken()
    {
        return $this->accessToken;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function hasExpired()
    {
        $expires = $this->getExpires();
        if (empty($expires)) {
            throw new \Exception('"expires" is not set on the token');
        }
        return $expires < time();
    }
}
