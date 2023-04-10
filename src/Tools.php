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

namespace PrestaScan;

class Tools
{
    public static function saveReport($reportFile, $results = null, $filters = null, $error = false)
    {
        $report['date_report'] = time();
        $report['error'] = $error;
        $report['report'] = array();
        $report['report']['filters'] = empty($filters) ? null : $filters;
        $report['report']['results'] = empty($results) ? null : $results;
        file_put_contents($reportFile, serialize($report));
    }

    public static function getModuleList()
    {
        // Do not use SQL request on ps_module as it will not list modules that are not installed

        // Will return a list of modules on disk, but ... not only.
        // Despite the fonction name, PrestaShop also returns a list of modules retrived from the webservice for marketing purpose
        $prestaShopModules = \Module::getModulesOnDisk(true);
        // The function has known issues to identify the state of a module (enable or disabled AND installed or not installed)
        // We do not trust this function and we will rely directly of the ps_module table to check the state.
        // We will also not filter by shop ID and will check if the module is at least enable in one shop.
        $sql = "SELECT module.id_module, module_shop.id_module module_shop_id, module.name
            FROM `"._DB_PREFIX_."module` module
            LEFT JOIN `"._DB_PREFIX_."module_shop` module_shop ON module_shop.id_module = module.id_module";
        $listEnabledOrInstalledModules = \Db::getInstance()->executeS($sql);

        foreach ($prestaShopModules as $key => $aModule) {
            if (isset($aModule->not_on_disk) && (int)$aModule->not_on_disk === 1) {
                unset($prestaShopModules[$key]);
                continue;
            }
            $active = false;
            $installed = false;
            foreach ($listEnabledOrInstalledModules as $moduleArray) {
                if ((int)$moduleArray['id_module'] === (int)$aModule->id) {
                    $installed = true;
                    $active = $moduleArray['module_shop_id'] === NULL ? false : true;
                    break;
                }
            }
            $list[] = array(
                'name' => $aModule->name,
                'active' => $active,
                'version' => $aModule->version,
                'installed' => $installed,
            );
            $prestaShopModules[$key]->active = $active;
        }
        return array(
            'allModules' => $prestaShopModules,
            'modulesOnDisk' => $list
        );
    }

    public static function getHashByName($hashName, $key)
    {
        return md5(_COOKIE_KEY_.$hashName.$key);
    }

    public static function displayErrorAndDie($code, $message = null)
    {
        http_response_code($code);
        if ($message) {
            die($message);
        }
        exit();
    }

    public static function getShopUrl()
    {
        // @todo :
        // - We need the FO URLs, however this function might be called from the BO. The FO and BO URL might be different...
        // - The module doesn't need to display different configuration/results for each shop, so we need to retrive the default shop URL ? Check if maintenance ?
        // Note : It should support localhost with custom port
        
        $protocol = (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) ? 'https://' : 'http://';
        $server = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 81 ? ':'.$_SERVER['SERVER_PORT'] : '';
        return $protocol.$server.$port;
    }

    public static function deleteReport($filename)
    {
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public static function getPrestashopVersion()
    {
        return _PS_VERSION_;
    }

    public static function resetModuleConfigurationAndCache($uninstall = false)
    {
        \Configuration::deleteByName('PRESTASCAN_REFRESH_TOKEN');
        \Configuration::deleteByName('PRESTASCAN_ACCESS_TOKEN_EXPIRE');
        \Configuration::deleteByName('PRESTASCAN_ACCESS_TOKEN');
        \Configuration::deleteByName('PRESTASCAN_ACCESS_CLIENT_ID');
        \Configuration::deleteByName('PRESTASCAN_ACCESS_CLIENT_SECRET');
        \Configuration::deleteByName('PRESTASCAN_TEST_MODE_OAUTH');
        \Configuration::deleteByName('PRESTASCAN_SCAN_PROGRESS');
        \Configuration::deleteByName('PRESTASCAN_UPDATE_VERSION_AVAILABLE');
        \Configuration::deleteByName('PRESTASCAN_HAS_UPDATE_VERSION');
        \Configuration::deleteByName('PRESTASCAN_LAST_VERSION_CHECK');

        if ($uninstall) {
            \Configuration::deleteByName('PRESTASCAN_SEC_HASH');
            \Configuration::deleteByName('PRESTASCAN_WEBCRON_TOKEN');
        }
        
        self::deleteCacheFiles();
    }

    public static function printAjaxResponse($success, $error, $statusText = '', $data = false)
    {
        die(\Tools::jsonEncode(
            array(
                'success' => $success,
                'error' => $error,
                'statusText' => $statusText,
                'data' => $data,
            )
        ));
    }
    
    public static function deleteCacheFiles()
    {
        $fullPath = self::getCachePath();
        array_map('unlink', glob( "$fullPath*.cache"));
        return true;
    }

    public static function getCachePath()
    {
        return _PS_MODULE_DIR_."prestascansecurity/cache/";
    }

    public static function getFormattedModuleOnDiskList()
    {
        $listOfModules = self::getModuleList();
        // All modules retrieved by PrestaShop API (includes modules on disk and not on disk (not in /modules))
        $listOfallModules = $listOfModules['allModules'];
        // Only modules on list, but with missing data
        $modulesOnDisk = $listOfModules['modulesOnDisk'];

        $formattedList = array();
        foreach ($listOfallModules as $aModule) {
            foreach ($modulesOnDisk as $key => $aModuleOnDisk) {
                if ($aModule->name !== $aModuleOnDisk['name']) {
                    continue;
                }

                $formattedList[$aModuleOnDisk['name']] = array(
                    'name' => $aModuleOnDisk['name'],
                    'version' => $aModuleOnDisk['version'],
                    'displayName' => $aModule->displayName,
                    'description' => $aModule->description,
                    'author' => $aModule->author,
                    'active' => $aModuleOnDisk['active'] ? true : false,
                    'installed' => $aModuleOnDisk['installed'],
                );

                if (isset($aModule->module_key) && $aModule->module_key !== '') {
                    // We add the module key for the module that have one (for addons requests)
                    $formattedList[$aModuleOnDisk['name']][] = $aModule->module_key;
                }

                unset($modulesOnDisk[$key]);
            }
        }

        return $formattedList;
    }

    public static function isContainingPerformedScan($scans)
    {
        foreach ($scans as $scan) {
            if ($scan !== false) {
                return true;
            }
        }
        return false;
    }

    public static function isContainingOutdatedScan($scans, $month = 1)
    {
        foreach ($scans as $aScan) {
            if (!$aScan) {
                // Scan not performed
                continue;
            }
            if (self::isScanOutDated($aScan['summary']['date'], $month)) {
                return true;
            }
        }
        return false;
    }

    public static function isScanOutDated($date, $month = 1)
    {
        $outdated = false;
        if (!empty($date)) {
            $date_scan = strtotime($date);
            if ($date_scan <= strtotime('-'.(int)$month.' month')) {
                $outdated = true;
            }
        }
        return $outdated;
    }

    public static function formatDateString($date)
    {
        $formattedDate = "";
        if (!empty($date)) {
            $formattedDate = date('j F Y', strtotime($date));
        }
        return $formattedDate;
    }

    public static function getOldestScan($scans)
    {
        if (count($scans) === 1) {
            return $scans[0];
        }

        // Set the initial value of the oldest scan and date
        $oldestScan = null;
        $oldestDate = null;

        // Loop through the results array
        foreach ($scans as $aScan) {
            if (!$aScan) {
                // Scan not performed
                continue;
            }

            // Check if the oldest date is null or the aScan's date is older
            $date = $aScan['summary']['date'];
            if ($oldestDate === null || $date < $oldestDate) {
                // Update the oldest scan and date
                $oldestScan = $aScan;
                $oldestDate = $date;
            }
        }

        // Return the oldest scan (or the first one if none are found)
        return is_null($oldestScan) ? $scans[0] : $oldestScan;
    }

    public static function getScanWithHighestCriticity($scans)
    {
        $highestCriticityScan = null;
        $highestCriticityLevel = -1;

        $criticityLevels = array(
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1
        );

        foreach ($scans as $scan) {
            if (!$scan) {
                continue;
            }
            //var_dump($scan['summary']);
            if (!isset($scan['summary']['scan_result_criticity'])) {
                var_dump($scan['summary']);
            }
            $scanCriticity = $scan['summary']['scan_result_criticity'];

            if (isset($criticityLevels[$scanCriticity]) && $criticityLevels[$scanCriticity] > $highestCriticityLevel) {
                $highestCriticityScan = $scan;
                $highestCriticityLevel = $criticityLevels[$scanCriticity];
            }
        }

        return is_null($highestCriticityScan) ? $scans[0] : $highestCriticityScan;
    }

}
