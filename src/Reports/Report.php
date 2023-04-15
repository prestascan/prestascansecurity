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

use PrestaScan\Exception\JobAlreadyInProgressException;

class Report
{
    public $reportName;

    private $cacheReports = array();

    private static $reportList = array(
        "non_standards_files",
        "added_or_modified_core_files",
        "infected_files",
        "directories_listing",
        "modules_vulnerabilities",
        "core_vulnerabilities",
        "modules_unused"
    );

    public function __construct()
    {
        $cacheDirectory = \PrestaScan\Tools::getCachePath();

        $cacheHash =  \Configuration::get('PRESTASCAN_SEC_HASH');
        // We create a hash for the cache files (to avoid direct access by guessing the name of the file)
        $tokenCache = \PrestaScan\Tools::getHashByName("cacheHash", $cacheHash);

        foreach (self::$reportList as $aReportName) {
            $this->cacheReports[$aReportName] = $cacheDirectory.$aReportName."_".$tokenCache.".cache";
        }
    }

    private function checkIfAlreadyInProgress()
    {
        // Check if job already in progress
        if (\PrestaScanQueue::isJobAlreadyInProgress($this->reportName)) {
            throw new JobAlreadyInProgressException("Job already in progress");
        }
    }

    private function checkReportResponse($response)
    {
        if (!isset($response['job_id'])) {
            // Error
            throw new \Exception(
                "The API was not able to handle the request with the following error : Missing job_id"
                .". Please try again or contact support for assistance."
            );
        }
    }

    private function addJobToQueue($jobId, $jobData)
    {
        if (!\PrestaScanQueue::addJob(
            $jobId,
            $this->reportName,
            json_encode($jobData)
        )) {
            throw new \Exception(
                "The scan wasn't able to save your request. Please try again or contact support for assistance."
            );
        }
    }

    protected function generateReport($request, $payload)
    {
        $this->checkIfAlreadyInProgress();

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

    public static function getReportsListName()
    {
        return self::$reportList;
    }
}
