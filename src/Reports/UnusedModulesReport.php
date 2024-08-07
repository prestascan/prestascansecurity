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

class UnusedModulesReport extends Report
{
    public $reportName = 'modules_unused';

    public function generate($automatic = false, $automaticScanId = '')
    {
        $moduleStatusReport = $this->getModulesByStatus();
        $postBody = array(
            'not_installed' => $moduleStatusReport['not_installed'],
            'disabled' => $moduleStatusReport['disabled'],
            'automatic' => $automatic,
            'automatic_scan_id' => $automaticScanId,
        );

        $request = new \PrestaScan\Api\Request(
            'prestascan-api/v1/scan/modules/unused',
            'POST',
            $postBody
        );

        $jobData = array('count_modulesOnDisk' => $moduleStatusReport['count_modulesOnDisk'], 'automatic' => $automatic);
        return parent::generateReport($request, $jobData, $automatic);
    }

    public function save($payload, $jobData)
    {
        $jobData = json_decode($jobData, true);

        $reportSummary = [];
        $reportSummary['scan_result_ttotal'] = count($payload['result']['disabled']) + count($payload['result']['not_installed']);
        $reportSummary['scan_result_total'] = $jobData['count_modulesOnDisk'];
        $reportSummary['total_disabled_modules'] = count($payload['result']['disabled']);
        $reportSummary['total_uninstalled_modules'] = count($payload['result']['not_installed']);

        $criticity = 'low';
        if ($reportSummary['scan_result_ttotal'] > 10) {
            $criticity = 'high';
        } elseif ($reportSummary['scan_result_ttotal'] > 5) {
            $criticity = 'medium';
        }
        $reportSummary['scan_result_criticity'] = $criticity;

        return parent::saveReport($payload['completion_date'], $reportSummary, $payload);
    }

    private function getModulesByStatus()
    {
        $modulesDisabled = []; // A list of modules disabled (@todo : Multishop ?)
        $modulesNotInstalled = []; // A list of modules not installed but on disk (in /modules)

        $allModulesOnDisk = \PrestaScan\Tools::getFormattedModuleOnDiskList();
        foreach ($allModulesOnDisk as $key => $aModuleOnDisk) {
            if ($aModuleOnDisk['installed'] && !$aModuleOnDisk['active']) {
                $modulesDisabled[] = $allModulesOnDisk[$key];
            } elseif (!$aModuleOnDisk['installed']) {
                $modulesNotInstalled[] = $allModulesOnDisk[$key];
            }
        }

        $countModulesOnDisk = count($allModulesOnDisk);

        return array(
            'not_installed' => $modulesNotInstalled,
            'disabled' => $modulesDisabled,
            'count_modulesOnDisk' => $countModulesOnDisk
        );
    }
}
