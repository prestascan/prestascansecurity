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

use PrestaScan\Exception\JobAlreadyInProgressException;

class Report
{
    public $reportName;

    private $cacheReports = [];

    private static $reportList = array(
        'non_standards_files',
        'added_or_modified_core_files',
        'infected_files',
        'directories_listing',
        'modules_vulnerabilities',
        'core_vulnerabilities',
        'modules_unused',
    );

    private $cacheListDismiss = [];
    private static $listDismiss = array(
        'non_standards_files_dismiss',
        'added_or_modified_core_files_dismiss',
        'infected_files_dismiss',
        'directories_listing_dismiss',
        'modules_vulnerabilities_dismiss',
        'core_vulnerabilities_dismiss',
        'modules_unused_dismiss',
    );

    public function __construct()
    {
        $cacheDirectory = \PrestaScan\Tools::getCachePath();

        $cacheHash = \Configuration::get('PRESTASCAN_SEC_HASH');
        // We create a hash for the cache files (to avoid direct access by guessing the name of the file)
        $tokenCache = \PrestaScan\Tools::getHashByName('cacheHash', $cacheHash);

        foreach (self::$reportList as $aReportName) {
            $this->cacheReports[$aReportName] = $cacheDirectory . $aReportName . '_' . $tokenCache . '.cache';
        }
        foreach (self::$listDismiss as $aReportName) {
            $this->cacheListDismiss[$aReportName] = $cacheDirectory . $aReportName . '_' . $tokenCache . '.cache';
        }
    }

    private function checkIfAlreadyInProgress($automatic = false)
    {
        // If it's an automatic scan :
        // We force all "PROGRESS", "TORETRIEVE" and "SUGGEST_CANCEL" scans that are running for more than 10 minutes to "CANCEL"
        if ($automatic) {
            $jobId = \PrestaScanQueue::getJobAlreadyInProgress($this->reportName, 10);
            if ($jobId) {
                \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['CANCEL']);
            }
        }
        // Check if job already in progress
        if (\PrestaScanQueue::isJobAlreadyInProgress($this->reportName)) {
            throw new JobAlreadyInProgressException('Job already in progress');
        }
    }

    private function checkReportResponse($response)
    {
        if (!isset($response['job_id'])) {
            // Error
            $errorMsg = 'The API was not able to handle the request with the following error : Missing job_id. '
                        .'Please try again or contact support for assistance.';
            throw new \Exception($errorMsg);
        }
    }

    private function addJobToQueue($jobId, $jobData)
    {
        if (!\PrestaScanQueue::addJob(
            $jobId,
            $this->reportName,
            json_encode($jobData)
        )) {
            throw new \Exception('The scan wasn\'t able to save your request. Please try again or contact support for assistance.');
        }
    }

    protected function generateReport($request, $payload, $automatic = false)
    {
        $this->checkIfAlreadyInProgress($automatic);

        $response = $request->getResponse();

        $this->addJobToQueue($response['job_id'], $payload);

        return true;
    }

    protected function saveReport($completionDate, $summary, $data)
    {
        $summary['date'] = date('Y-m-d H:i:s', strtotime($completionDate));
        $summary['scan_type'] = $this->reportName;
        $data['summary'] = $summary;
        return \Prestascan\Tools::saveReport($this->cacheReports[$this->reportName], $data);
    }

    public function deleteReportCompleted()
    {
        return \PrestascanQueue::deleteCompletedByActionName($this->reportName)
            && \Prestascan\Tools::deleteReport($this->cacheReports[$this->reportName]);
    }

    public function getReports()
    {
        return $this->cacheReports;
    }

    public function getDismissedCacheFiles()
    {
        return $this->cacheListDismiss;
    }

    public static function getReportsListName()
    {
        return self::$reportList;
    }

    public function updateReportCache($reportName, $report)
    {
        file_put_contents($this->cacheReports[$reportName], serialize($report));
    }

    public function updateDismissedEntitiesList($actionReport, $value, $type = '', $vulnerabilitiesCount = '')
    {
        return \Prestascan\Tools::updateDismissedEntitiesList(
            $this->cacheReports[$this->reportName],
            $actionReport,
            $value,
            $type,
            $vulnerabilitiesCount
        );
    }

    public function updateDismissedEntitiesStatus($results, $reportName)
    {
        switch ($reportName) {
            case 'core_vulnerabilities':
                $countdismiss = 0;
                foreach ($results['result'] as $k => $result) {
                    if (isset($results['result'][$k]['is_dismissed']) && $results['result'][$k]['is_dismissed']) {
                        $countdismiss++;
                        $results['summary']['scan_result_total'] -= 1;
                        $results['summary']['scan_result_ttotal'] -= 1;
                        if ($result['severity']['severity_value'] == 'Critical') {
                            $results['summary']['total_critical'] -= 1;
                        } elseif ($result['severity']['severity_value'] == 'High') {
                            $results['summary']['total_high'] -= 1;
                        } elseif ($result['severity']['severity_value'] == 'Medium') {
                            $results['summary']['total_medium'] -= 1;
                        } else {
                            $results['summary']['total_low'] -= 1;
                        }
                    }
                }
                if ($countdismiss == count($results['result'])) {
                    $results['summary']['scan_result_criticity'] = '';
                }

                break;
            case 'modules_vulnerabilities':
                $count_dismiss_vulnerable = 0;
                $count_dismiss_update = 0;
                foreach ($results['vulnerable'] as $k => $result) {
                    if (isset($result["is_dismissed"]) && $result["is_dismissed"]) {
                        $count_dismiss_vulnerable++;
                        $results['summary']['scan_result_ttotal'] -= 1;
                        $results['summary']['total_vulnerable'] -= 1;
                        $results['vulnerable'][$k]['count_vulerability'] = $count_dismiss_vulnerable;
                    }
                }
                foreach ($results['module_to_update'] as $k => $result) {
                    if (isset($result['is_dismissed']) && $result['is_dismissed']) {
                        $results['summary']['scan_result_ttotal'] -= 1;
                        $results['summary']['total_module_to_update'] -= 1;
                        $count_dismiss_update++;
                    }
                }
                if (count($results['vulnerable']) == $count_dismiss_vulnerable
                    && count($results['module_to_update']) == $count_dismiss_update) {
                    $results['summary']['scan_result_criticity'] = '';
                }

                break;
            case 'modules_unused':
                $count_dismiss = 0;
                foreach ($results['result']['not_installed'] as $k => $result) {
                    if (isset($results['result']['not_installed'][$k]['is_dismissed']) && $results['result']['not_installed'][$k]['is_dismissed']) {
                        $results['summary']['scan_result_ttotal'] -= 1;
                        $results['summary']['total_uninstalled_modules'] -= 1;
                        $count_dismiss++;
                    }
                }
                foreach ($results['result']['disabled'] as $k => $result) {
                    if (isset($results['result']['disabled'][$k]['is_dismissed']) && $results['result']['disabled'][$k]['is_dismissed']) {
                        $results['summary']['scan_result_ttotal'] -= 1;
                        $results['summary']['total_disabled_modules'] -= 1;
                        $count_dismiss++;
                    }
                }
                if ($count_dismiss == (count($results['result']['disabled']) + count($results['result']['not_installed']))) {
                    $results['summary']['scan_result_criticity'] = '';
                }

                break;
            case 'directories_listing':
                $count_dismiss = 0;
                $count_failed = 0;
                foreach ($results['result'] as $k => $result) {
                    if ($result[0]['status'] != 'pass') {
                        $count_failed++;
                    }
                    if (isset($results['result'][$k][0]['is_dismissed']) && $results['result'][$k][0]['is_dismissed']) {
                        $count_dismiss++;
                        $results['summary']['scan_result_ttotal'] -= 1;
                        $results['summary']['scan_result_fail_total'] -= 1;
                        $results['summary']['scan_result_pass_total'] += 1;
                    }
                }
                if ($count_dismiss == $count_failed) {
                    $results['summary']['scan_result_criticity'] = '';
                }
                $results['summary']['count_failed'] = $count_failed;
                $results['summary']['count_dismissed'] = $count_dismiss;

                break;
            default:

                break;
        }
        return $results;
    }

    public function setScanStatus($type, $inProgress)
    {
        $progressScans = \Configuration::get('PRESTASCAN_SCAN_PROGRESS');

        if (empty($progressScans)) {
            // Config not existing, we set the default value for each report
            $progressScans = [];
            foreach (self::getReportsListName() as $aReportName) {
                $progressScans[$aReportName] = false;
            }
        } else {
            $progressScans = json_decode($progressScans, true);
        }

        // We set the current scan status
        $progressScans[$type] = $inProgress;

        \Configuration::updateGlobalValue('PRESTASCAN_SCAN_PROGRESS', json_encode($progressScans));
    }
}
