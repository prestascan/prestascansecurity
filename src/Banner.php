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

class Banner
{
    public static function getBanner()
    {
        $bannerResponse = null;
        try {
            // Only query the server, if no previous banner check was previously done
            // OR if the last banner check is older than 24h
            $lastBannerCheck = \Configuration::get('PRESTASCAN_BANNER_LAST_CHECK');
            if (empty($lastBannerCheck) || (strtotime(date('Y-m-d H:i:s')) - strtotime($lastBannerCheck)) > 86400) {
                \Configuration::updateGlobalValue('PRESTASCAN_BANNER_LAST_CHECK', (new \DateTime())->format('Y-m-d H:i:s'));
                $apiRequest = new \PrestaScan\Api\Request(
                    'prestascan-api/v1/banner',
                    'GET'
                );
                $apiResponse = $apiRequest->getResponse();

                // Check the response format
                if (!isset($apiResponse['image_url']) ||
                    !isset($apiResponse['cta']) ||
                    !isset($apiResponse['enable'])) {
                    \Configuration::deleteByName('PRESTASCAN_BANNER_RESPONSE');
                    return null;
                }

                // Check if the banner is enabled
                if (!$apiResponse['enable']) {
                    \Configuration::deleteByName('PRESTASCAN_BANNER_RESPONSE');
                    return null;
                }

                \Configuration::updateGlobalValue('PRESTASCAN_BANNER_RESPONSE', json_encode($apiResponse));
            } else {
                $savedBannerResponse = \Configuration::get('PRESTASCAN_BANNER_RESPONSE', null);
                // The banner response is a JSON, so decode it beforehand
                if (!empty($savedBannerResponse)) {
                    $bannerResponse = json_decode($savedBannerResponse);
                }
            }
        } catch (\Exception $e) {
            $bannerResponse = null;
        }

        return $bannerResponse;
    }
}
