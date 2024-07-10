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

namespace PrestaScan\Reports;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VulnerableModulesReport extends Report
{
    public $reportName = 'modules_vulnerabilities';

    public function generate($automatic = false, $automaticScanId = '')
    {
        $sanatizedList = [];

        // Retrieve the list of all modules on list
        $allModulesOnDisk = \PrestaScan\Tools::getFormattedModuleOnDiskList();

        // Save the module list in cache (we need it for webhook alerts to reduce the charge)
        $moduleRawCacheFile = \PrestaScan\Tools::getModuleRawCacheFile();
        file_put_contents($moduleRawCacheFile, serialize($allModulesOnDisk));

        // Complete the data with the module_key
        foreach ($allModulesOnDisk as $key => $aModuleOnDisk) {
            $sanitizedModule = array(
                'name' => $aModuleOnDisk['name'],
                'version' => $aModuleOnDisk['version'],
            );
            if (isset($aModuleOnDisk['module_key'])) {
                $sanitizedModule['module_key'] = $aModuleOnDisk['module_key'];
            }
            $sanatizedList[] = $sanitizedModule;
        }

        // API call to send the list of modules to analyse
        $postBody = array(
            'ps_version' => \PrestaScan\Tools::getPrestashopVersion(),
            'modules' => $sanatizedList,
            'automatic' => $automatic,
            'automatic_scan_id' => $automaticScanId,
        );
        $request = new \PrestaScan\Api\Request(
            'prestascan-api/v1/scan/modules/vulnerabilities',
            'POST',
            $postBody
        );

        $jobData = array('count_modules_scanned' => count($sanatizedList), 'automatic' => $automatic);
        return parent::generateReport($request, $jobData, $automatic);
    }

    public function save($payload, $jobData)
    {
        $data = [];
        $data['vulnerable'] = [];
        $data['module_to_update'] = [];
        // Retrieve additionnal job data
        $jobData = json_decode($jobData, true);

        if (is_array($payload)
            && isset($payload['result'])
            && is_array($payload['result'])) {
            // Check if the version is concerned by the alert
            $moduleRawCacheFile = \PrestaScan\Tools::getModuleRawCacheFile();

            if (file_exists($moduleRawCacheFile)) {
                // This cache file should always exists when a scan is triggered.
                // Tho, this is a fallback function
                $allModulesOnDisk = unserialize(file_get_contents($moduleRawCacheFile));
            } else {
                // We first add missing informations concerning the modules (installed/enabled)
                // We get the list of modules in the site
                $allModulesOnDisk = \PrestaScan\Tools::getFormattedModuleOnDiskList();
            }

            foreach ($payload['result'] as $k => $moduledata) {
                foreach ($allModulesOnDisk as $key => $aModuleOnDisk) {
                    if ($aModuleOnDisk['name'] !== $moduledata['name']) {
                        continue;
                    }
                    $moduledata['active'] = $aModuleOnDisk['active'];
                    $moduledata['installed'] = $aModuleOnDisk['installed'];
                    $moduledata['displayName'] = $aModuleOnDisk['displayName'];
                    $moduledata['description'] = $aModuleOnDisk['description'];
                    $moduledata['author'] = $aModuleOnDisk['author'];

                    unset($allModulesOnDisk[$key]);
                    break;
                }

                if (isset($moduledata['require_update'])
                    && $moduledata['require_update']
                    && !count($moduledata['vulnerabilities'])) {
                    // Module to update, but no public vulnerability
                    $moduledata['last_update_expire'] = false;
                    if (isset($moduledata['last_update']) && $moduledata['last_update'] != '') {
                        if (strtotime($moduledata['last_update']) < strtotime('-3 years')) {
                            $moduledata['last_update_expire'] = true;
                        }
                    }
                    $data['module_to_update'][] = $moduledata;
                    continue;
                }

                if (!is_array($moduledata['vulnerabilities']) || !count($moduledata['vulnerabilities'])) {
                    // No vulnerabilities for this module
                    continue;
                }

                // We check what is the highest criticity for the module (which may have multiple vulnerabilities)
                $moduledata['criticity'] = 'low';
                foreach ($moduledata['vulnerabilities'] as $vulnerability) {
                    $criticity = $vulnerability['criticity'];
                    if ($criticity == 'high' || $criticity == 'critical') {
                        // There is at least one high vulnerability, so we stop here, no need to continue.
                        $moduledata['criticity'] = 'high';
                        break;
                    } elseif ($criticity == 'medium' && $moduledata['criticity'] == 'low') {
                        // We have find one medium vulnerability.
                        $moduledata['criticity'] = 'medium';
                    }
                    $moduledata['author_discovery'] = $vulnerability['author_discovery'];
                }
                $data['vulnerable'][] = $moduledata;
            }
        }

        // We sort the module to update to display first modules that are not updated on addons since few years
        $this->array_sort_by_column($data['module_to_update'], 'last_update_expire', SORT_DESC);

        $reportSummary = [];
        $reportSummary['scan_result_total'] = (int) $jobData['count_modules_scanned'];
        $reportSummary['scan_result_criticity'] = $this->calculateModuleSeverity($data);
        $reportSummary['scan_result_ttotal'] = count($data['vulnerable']) + count($data['module_to_update']);
        $reportSummary['total_vulnerable'] = count($data['vulnerable']);
        $reportSummary['total_module_to_update'] = count($data['module_to_update']);

        $report = parent::saveReport($payload['completion_date'], $reportSummary, $data);

        // Dismiss all existing webhook alerts
        \PrestaScanVulnAlerts::dismissAll();

        return $report;
    }

    private function calculateModuleSeverity($data)
    {
        if (empty($data['vulnerable']) && empty($data['module_to_update'])) {
            // No vulnerabilities found and no modules to update
            return 'low';
        }

        if (empty($data['vulnerable'])) {
            // No vulnerabilities found but modules not up to date
            return 'medium';
        }

        // There are modules not up to date, so the criticity is medium by default.
        $maximalCriticity = 'medium';
        // And now we continue to check the criticity of the scan

        $vulnerableModules = $data['vulnerable'];
        foreach ($vulnerableModules as $aModule) {
            $criticity = $aModule['criticity'];
            if ($criticity == 'high' || $criticity == 'critical') {
                // There is at least one high or critical vulnerability, so we stop here, no need to continue.
                return 'high';
            }
        }

        return $maximalCriticity;
    }

    private function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);
    }
}
