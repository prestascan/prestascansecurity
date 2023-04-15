<?php
/*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
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

namespace PrestaScan\Api;

use PrestaScan\OAuth2\Oauth;
use PrestaScan\Api\Response;

class Request
{

    private $options = array();

    private $OAuth;

    private $type;

    private $endpoint;

    public function __construct($endpoint, $type = "GET", $postBody = null)
    {
        // @todo check Type
        // @todo, postBody should be null or an array

        $this->options = $this->getOptions($postBody);

        $this->OAuth = new Oauth();
        $this->type = $type;
        $this->endpoint = $endpoint;
    }

    protected function getOptions($postBody)
    {
        $options['headers'] = array();
        $options['headers']['Content-Type'] = 'application/json;charset=UTF-8';
        $options['headers']['Accept'] = 'application/json';

        if (!empty($postBody)) {
            $options['body'] = json_encode($postBody);
        }

        return $options;
    }

    public function getResponse()
    {
        $response = $this->OAuth->getAuthenticatedRequestWithResponse(
            $this->type,
            $this->endpoint,
            $this->options
        );

        Response::checkResponse($response);
        return Response::getBody($response);
    }
}
