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

class DirectoriesProtectionReport extends Report
{
    public $reportName = 'directories_listing';

    public function generate()
    {
        // Create a test directory (to check if a directory without protection is by default blocked during the scan)
        if (!is_dir(_PS_ROOT_DIR_ . '/prestascan_test')) {
            mkdir(_PS_ROOT_DIR_ . '/prestascan_test');
        }
        $dirs = array_filter(glob(_PS_ROOT_DIR_ . '/*'), 'is_dir');
        foreach ($dirs as $k => $dir) {
            $directories[] = str_replace(_PS_ROOT_DIR_ . '/', '', $dir);
        }
        $postBody = array(
            'directories' => $directories,
        );
        $request = new \PrestaScan\Api\Request(
            'prestascan-api/v1/scan/scan-directories-protection',
            'POST',
            $postBody
        );

        $jobData = array('count_directories_scanned' => count($dirs));
        return parent::generateReport($request, $jobData);
    }

    public function save($payload)
    {
        $passCount = 0;
        $failCount = 0;
        foreach ($payload['result'] as $result) {
            if ($result[0]['status'] == 'pass') {
                $passCount++;
            } else {
                $failCount++;
            }
        }
        $reportSummary = array();

        $reportSummary['scan_result_total'] = count($payload['result']);
        $reportSummary['scan_result_ttotal'] = $failCount;
        $reportSummary['scan_result_fail_total'] = $failCount;
        $reportSummary['scan_result_pass_total'] = $passCount;
        $reportSummary['scan_result_criticity'] = $failCount > 3 ? 'high' : ($failCount > 0 ? 'medium' : '');

        // We remove the temp directory we created.
        if (is_dir(_PS_ROOT_DIR_ . '/prestascan_test')) {
            rmdir(_PS_ROOT_DIR_ . '/prestascan_test');
        }

        return parent::saveReport($payload['completion_date'], $reportSummary, $payload);
    }
}
