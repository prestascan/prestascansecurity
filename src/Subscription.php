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

namespace PrestaScan;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Subscription
{
    public static function getSubscription($forceUpdate = false)
    {
        $subscriptionResponse = null;
        try {
            $OAuth = new \PrestaScan\OAuth2\Oauth();
            if (!$OAuth->getAccessTokenObj()) {
                \Configuration::updateGlobalValue('PRESTASCAN_SUBS_STATE', 0);
                \Configuration::updateGlobalValue('PRESTASCAN_SUBS_LAST_CHECK', (new \DateTime())->format('Y-m-d H:i:s'));
                return 0;
            }
            $lastSubscriptionCheck = \Configuration::get('PRESTASCAN_SUBS_LAST_CHECK');
            if (empty($lastSubscriptionCheck)
                || (strtotime(date('Y-m-d H:i:s')) - strtotime($lastSubscriptionCheck)) > 600
                || $forceUpdate
            ) {
                $route = 'prestascan-api/v1/subscription/check';
                if ($forceUpdate) {
                    $route .= "/refresh";
                }
                $apiRequest = new \PrestaScan\Api\Request(
                    $route,
                    'GET'
                );
                $apiResponse = $apiRequest->getResponse();
                // Check the response format
                if (!isset($apiResponse['state'])
                ) {
                    \Configuration::updateGlobalValue('PRESTASCAN_SUBS_STATE', 0);
                    $subscriptionResponse = 0;
                } else {
                    $oldSubsState = \Configuration::get('PRESTASCAN_SUBS_STATE', 0);
                    \Configuration::updateGlobalValue('PRESTASCAN_SUBS_STATE', (int)$apiResponse['state']);
                    if ($oldSubsState != (int)$apiResponse['state']) {
                        \Configuration::deleteByName('PRESTASCAN_BANNER_RESPONSE');
                        \Configuration::deleteByName('PRESTASCAN_BANNER_LAST_CHECK');
                    }
                    $subscriptionResponse = (int)$apiResponse['state'];
                }
                \Configuration::updateGlobalValue('PRESTASCAN_SUBS_LAST_CHECK', (new \DateTime())->format('Y-m-d H:i:s'));
            } else {
                $subscriptionResponse = (int)\Configuration::get('PRESTASCAN_SUBS_STATE');
            }
        } catch (\Exception $e) {
            $subscriptionResponse = 0;
        }

        return $subscriptionResponse;
    }
}
