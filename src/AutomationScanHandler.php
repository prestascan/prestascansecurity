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

class AutomationScanHandler
{
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function handle($data)
    {
        $return = [];
        $report = null;
        switch ($data['scan_type']) {
            case 'core_vulnerabilities':
                $report = new \PrestaScan\Reports\CoreVulnerabilitiesReport();
                break;
            case 'directories_listing':
                $report = new \PrestaScan\Reports\DirectoriesProtectionReport();
                break;
            case 'modules_unused':
                $report = new \PrestaScan\Reports\UnusedModulesReport();
                break;
            case 'modules_vulnerabilities':
                $report = new \PrestaScan\Reports\VulnerableModulesReport();
                break;
            default:
                break;
        }
        if (!is_null($report)) {
            if (in_array($data['scan_type'], array('modules_unused', 'modules_vulnerabilities'))) {
                // We notice an issue with the module `gauthenticator` that `exit()` the script when retrieving the
                // instance with `getInstanceByName(gauthenticator)`. This is due to a custom definition of the
                // module with `PS_ADMIN_DIR`. Therefore we force the value here no to block the scan.
                if (!defined('PS_ADMIN_DIR')) {
                    define('PS_ADMIN_DIR', '_PS_ADMIN_DIR_');
                }
            }
            // Default parameter are false, ''. true, job_id to indicate this is an automatic scan
            $result = $report->generate(true, $data['automatic_scan_result_id']);
            $report->setScanStatus($data['scan_type'], true);
        }

        return $result;
    }
}
