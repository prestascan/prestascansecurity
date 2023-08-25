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
class AdminPrestascanSecurityReportsController extends ModuleAdminController
{
    /**
     * The `display_header` is mandatory for compatibility reasons with Context.
     * Do not remove it. It will trigger a fatal error on PrestaShop 1.7
     */
    public $display_header = false;
    public $display_footer = false;

    public function init()
    {
        $OAuth = new \PrestaScan\OAuth2\Oauth();
        $error = false;
        try {
            if (!$OAuth->getAccessTokenObj()) {
                $error = true;
            }
        } catch (\Exception $exp) {
            // An exception may occure when token values are invalid. This may happen with localoauth
            if (\Tools::getValue('action') === 'logout') {
                $this->ajaxProcessLogout();
            }
            $error = true;
        }

        if ($error) {
            self::dieWithError($this->module->l('To launch a scan please log in or create an account. Having an account allows us to securely perform scans on your behalf and deliver accurate results. Click \'Login\' on the top right corner to sign in or create a new account.', 'AdminPrestascanSecurityReportsController'));
        }

        // Check if an update is available
        $updateAvailable = Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE') ? true : false;
        if ($updateAvailable === true
            && Tools::getValue('action') !== 'updateModule'
            && Tools::getValue('action') !== 'logoutUser'
            && Tools::getValue('action') !== 'dismmissedAlert'
        ) {
            // When an update is available, we return an error message

            if (Tools::getValue('action') === 'checkScanJobsProgression') {
                // It's not a scan, no need to display the error.
                \PrestaScan\Tools::printAjaxResponse(true, false);
            }

            if (\Tools::getValue('action') != 'refreshModuleStatus') {
                \PrestaScan\Tools::printAjaxResponse(false, true, 'refresh_module_status_required');
            }
        }
    }

    private function setScanStatus($type, $inProgress)
    {
        $progressScans = Configuration::get('PRESTASCAN_SCAN_PROGRESS');

        if (empty($progressScans)) {
            // Config not existing, we set the default value for each report
            $progressScans = array();
            foreach (\PrestaScan\Reports\Report::getReportsListName() as $aReportName) {
                $progressScans[$aReportName] = false;
            }
        } else {
            $progressScans = json_decode($progressScans, true);
        }

        // We set the current scan status
        $progressScans[$type] = $inProgress;

        Configuration::updateGlobalValue('PRESTASCAN_SCAN_PROGRESS', json_encode($progressScans));
    }

    private function saveReport($job, $jobId, $payload)
    {
        \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['COMPLETED']);
        $this->setScanStatus($job['action_name'], false);
        if ($job['action_name'] === 'modules_vulnerabilities') {
            $report = new \PrestaScan\Reports\VulnerableModulesReport();
            $report->save($payload, $job['job_data']);
        } elseif ($job['action_name'] === 'directories_listing') {
            $report = new \PrestaScan\Reports\DirectoriesProtectionReport();
            $report->save($payload);
        } elseif ($job['action_name'] === 'core_vulnerabilities') {
            $report = new \PrestaScan\Reports\CoreVulnerabilitiesReport();
            $report->save($payload);
        } elseif ($job['action_name'] === 'modules_unused') {
            $report = new \PrestaScan\Reports\UnusedModulesReport();
            $report->save($payload, $job['job_data']);
        }
    }

    private function checkPayloadForErrorAndDie($job, $jobId, $payload)
    {
        if (is_array($payload)
            && isset($payload['result'])
            && isset($payload['result']['success'])
            && $payload['result']['success'] === false) {
            \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['ERROR'], $payload['result']['error']);
            $this->setScanStatus($job['action_name'], false);

            switch ($payload['result']['error']) {
                case 'timeout':
                    $errorMessage = $this->module->l('Error while processing one of your scans. Detail : timeout. There might be too much data to process for your scan. Please try again.', 'AdminPrestascanSecurityReportsController');
                    break;
                case 'manually_failed':
                    $errorMessage = $this->module->l('Error while processing one of your scans. Detail : Scan manually stopped.', 'AdminPrestascanSecurityReportsController');
                    break;
                case 'other_general_error_max_attempts':
                    $errorMessage = $this->module->l('Error while processing one of your scans. Detail : other_general_error_max_attempts. There might be too much data to process for your scan. Please try again.', 'AdminPrestascanSecurityReportsController');
                    break;
                case 'other_general_error':
                    $errorMessage = $this->module->l('Unknown server error while processing one of your scans. Please try again.', 'AdminPrestascanSecurityReportsController');
                    break;

                default:
                    $errorMessage = $this->module->l('Unknown error while processing one of your scans. Make sure your website is reachable by PrestaScan Security. Please try again.', 'AdminPrestascanSecurityReportsController');
                    break;
            }

            $suffixErrorMessage = ' ' . $this->module->l('If the error happens again, contact our support.', 'AdminPrestascanSecurityReportsController');

            self::dieWithError($errorMessage . $suffixErrorMessage);
        }
    }

    public function ajaxProcessCheckScanJobsProgression()
    {
        // Add a lock to avoid reports or API calls overwritting each others
        if (!\PrestaScan\Tools::createLockFile('report_jobs_progression.lock')) {
            \PrestaScan\Tools::printAjaxResponse(true, false);
        }

        $this->checkJobTooLongProgress();

        $jobs = \PrestaScanQueue::getJobsByState(\PrestaScanQueue::$actionname['TORETRIEVE']);
        $resultProgress = [];
        $scanCompleted = false;
        foreach ($jobs as $job) {
            $jobId = $job['jobid'];
            try {
                $payload = \PrestaScan\Api\Queue::check($jobId);
                if (isset($payload['error'])
                    && isset($payload['error']['code'])
                    && (int) $payload['error']['code'] === 200
                ) {
                    // Report not ready yet
                    // Nothing to do
                    continue;
                }

                $this->checkPayloadForErrorAndDie($job, $jobId, $payload);

                if (is_array($payload)) {
                    $this->saveReport($job, $jobId, $payload);
                    $scanCompleted = true;
                    \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['COMPLETED']);
                }
            } catch (\PrestaScan\Exception\TooManyAttempsException $exp) {
                // Rate limit
                self::dieWithError($this->module->l('You have reached the limit of number of attemps allowed for this scan today. Please try again in 24 hours.', 'AdminPrestascanSecurityReportsController'));
            } catch (\Exception $e) {                
                // @todo : $e->getCode() is the HTTP response code? That seem odd.
                // Code to review

                if ($e->getCode() == 400) {
                    \PrestaScanQueue::updateJob($jobId, 'error', $e->getMessage());
                    self::dieWithError($this->module->l('Scan processing error.', 'AdminPrestascanSecurityReportsController'));
                } elseif ($e->getCode() == 200) {
                    $resultProgress[] = $job['action_name'];
                    \PrestaScanQueue::updateJob($jobId);
                    continue;
                } else {
                    self::dieWithError($this->module->l('API Response error while checking progression of scans.', 'AdminPrestascanSecurityReportsController'));
                }
            }
        }
        
        if ($scanCompleted) {
            \PrestaScan\Tools::printAjaxResponse(true, false, $this->module->l('A scan just completed ! Please reload this page to view the report.', 'AdminPrestascanSecurityReportsController'));
        }

        if (count($resultProgress)) {
            \PrestaScan\Tools::printAjaxResponse(true, false, $this->module->l('Scan not finished yet', 'AdminPrestascanSecurityReportsController'));
        }
        \PrestaScan\Tools::printAjaxResponse(true, false);
    }

    public function ajaxProcessUnusedModulesActions()
    {
        try {
            $actionType = Tools::getValue('action_type');
            $moduleName = Tools::getValue('module_name');
            $message = $this->module->l('Module action not performed', 'AdminPrestascanSecurityReportsController');

            if (!Validate::isModuleName($moduleName)) {
                $error = $this->module->l('The module you are trying to edit doesn\'t seems to be valid. You may try refreshing the module list cache by starting a new scan of your Modules at-risk.', 'AdminPrestascanSecurityReportsController');
                self::dieWithError($error);
            }

            $module = Module::getInstanceByName($moduleName);
            if (!$module) {
                $error = $this->module->l('The module you are trying to delete/uninstall could not be found. Please try launching a new scan to refresh the modules cache.', 'AdminPrestascanSecurityReportsController');
                self::dieWithError($error);
            }

            switch (strtolower($actionType)) {
                case 'deletemodule' :
                    $retour = true;
                    // If the module is installed, we first uninstall it
                    if ($module::isInstalled($moduleName)) {
                        $retour = $module->uninstall();
                    }

                    // If there was no error during the uninstall, we remove the module
                    if ($retour) {
                        $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_ . $moduleName);

                        // update report file to change status
                        $this->updateUnusedModuleReportCache($module, 'deletemodule');

                        $message = $this->module->l('Module successfully deleted.', 'AdminPrestascanSecurityReportsController');
                        $message .= $this->module->l('To update the module report with the new shop data, you can perform a complete scan again.', 'AdminPrestascanSecurityReportsController');
                    } else {
                        $error = $this->module->l('Error trying to uninstall the module', 'AdminPrestascanSecurityReportsController');
                        self::dieWithError($error);
                    }
                    break;
                case 'uninstallmodule' :
                    if ($retour = $module->uninstall() ) {
                        // update report file to change status
                        $this->updateUnusedModuleReportCache($module, 'uninstallmodule');

                        $message = $this->module->l('Module successfully uninstalled.', 'AdminPrestascanSecurityReportsController');
                        $message .= $this->module->l('To update the module report with the new shop data, you can perform a complete scan again.', 'AdminPrestascanSecurityReportsController');
                    } else {
                        $error = $this->module->l('Error trying to uninstall the module', 'AdminPrestascanSecurityReportsController');
                        self::dieWithError($error);
                    }
                    break;
                default:
                    $message = $this->module->l('No action type performed', 'AdminPrestascanSecurityReportsController');
                    break;
            }
        } catch (Exception $e) {
            $error = $this->module->l('An unexpected error occurred when trying to delete/uninstall the module. You may try refreshing the module list cache by starting a new scan of your Modules at-risk.', 'AdminPrestascanSecurityReportsController');
            self::dieWithError($error);
        }

        \PrestaScan\Tools::printAjaxResponse($retour ? true : false, $retour ? false : true, $message);
    }

    protected function updateUnusedModuleReportCache($module, $action)
    {
        // Update report file to change status
        $moduleReport = new \PrestaScan\Reports\UnusedModulesReport();
        // We get the reports stored in cache
        $getReports = $moduleReport->getReports();
        // We retrieve the module_unused report
        $reportCacheFile = $getReports['modules_unused'];

        // If the file doesn't exist, we stop here (should nor happend anyway, no need for an error code)
        if (!is_file($reportCacheFile)) {
            return false;
        }

        $report = unserialize(file_get_contents($reportCacheFile));
        // If the report is empty or invalid, we stop here (should nor happend anyway, no need for an error code)
        if (empty($report) || !isset($report['report']) || !isset($report['report'])) {
            return false;
        }

        $results = $report['report']['results'];
        if ($action == 'uninstallmodule') {
            foreach ($results['result']['disabled'] as $key => $value) {
                if ($value['name'] == $module->name) {
                    array_push($results['result']['not_installed'], $value);
                    unset($results['result']['disabled'][$key]);
                    $results['summary']['total_disabled_modules']--;
                    $results['summary']['total_uninstalled_modules']++;
                    break;
                }
            }
        } elseif ($action == 'deletemodule') {
            // Uninstalled module are made of 2 differents lists
            // So we check if the module is present in the uninstalled module list first
            $moduleFound = false;
            foreach ($results['result']['disabled'] as $key => $value) {
                if ($value['name'] == $module->name) {
                    unset($results['result']['disabled'][$key]);
                    $results['summary']['total_disabled_modules']--;
                    $results['summary']['scan_result_ttotal']--;
                    $moduleFound = true;
                    break;
                }
            }

            // If the module is not present in the uninstalled modules, then we check in the other list
            if (!$moduleFound) {
                foreach ($results['result']['not_installed'] as $key => $value) {
                    if ($value['name'] == $module->name) {
                        unset($results['result']['not_installed'][$key]);
                        $results['summary']['total_uninstalled_modules']--;
                        $results['summary']['scan_result_ttotal']--;
                        break;
                    }
                }
            }
        }

        $report['report']['results'] = $results;
        $reportData = new \PrestaScan\Reports\Report();
        $reportData->updateReportCache('modules_unused', $report);
    }

    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }
        if (is_dir($dir)) {
            $objects = scandir($dir, SCANDIR_SORT_NONE);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        $this->recursiveDeleteOnDisk($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function ajaxProcessUpdateModule()
    {
        try {
            $update = new \PrestaScan\Update($this->context, $this->module);
            $update->processUpdateModule();
            Context::getContext()->cookie->__set('psscan_module_updated', true);
            \PrestaScan\Tools::printAjaxResponse(true, false);
        } catch (\Exception $exp) {
            // Error during the upgrade, return the error message
            self::dieWithError($this->module->l('Error during module update', 'AdminPrestascanSecurityReportsController'));
        }
    }

    public function ajaxProcessLogoutUser()
    {
        // Remove the data in the database
        \PrestaScanQueue::truncate();
        \PrestaScanVulnAlerts::truncate();
        // Delete cache files and configuration
        \PrestaScan\Tools::resetModuleConfigurationAndCache();
        \PrestaScan\Tools::printAjaxResponse(true, false);
    }

    public function ajaxProcessGenerateDirectoriesProtection()
    {
        $this->generateReport(\PrestaScan\Reports\DirectoriesProtectionReport::class);
    }

    public function ajaxProcessGenerateCoreVulnerabilities()
    {
        $this->generateReport(\PrestaScan\Reports\CoreVulnerabilitiesReport::class);
    }

    public function ajaxProcessUnusedModules()
    {
        $this->generateReport(\PrestaScan\Reports\UnusedModulesReport::class);
    }

    public function ajaxProcessGenerateModuleReport()
    {
        $this->generateReport(\PrestaScan\Reports\VulnerableModulesReport::class);
    }

    public function ajaxProcessGenerateModulesReport()
    {
        $this->generateReport(\PrestaScan\Reports\UnusedModulesReport::class, true);
        $this->generateReport(\PrestaScan\Reports\VulnerableModulesReport::class);
    }

    public function ajaxProcessGenerateVulnerabilitiesReport()
    {
        $this->ajaxProcessGenerateCoreVulnerabilities();
    }

    public function ajaxProcessGenerateFilesReport()
    {
        $this->ajaxProcessGenerateDirectoriesProtection();
    }

    public function ajaxProcessGenerateGlobalReport()
    {
        $this->generateReport(\PrestaScan\Reports\UnusedModulesReport::class, true);
        $this->generateReport(\PrestaScan\Reports\CoreVulnerabilitiesReport::class, true);
        $this->generateReport(\PrestaScan\Reports\DirectoriesProtectionReport::class, true);
        $this->generateReport(\PrestaScan\Reports\VulnerableModulesReport::class);
    }

    public function generateReport($classReport, $bath = false)
    {
        // Add a lock to avoid reports or API calls overwritting each others
        if (!\PrestaScan\Tools::createLockFile('report_generation.lock')) {
            \PrestaScan\Tools::printAjaxResponse(true, false);
        }

        try {
            $report = new $classReport();
            $report->generate();
            $this->setScanStatus($report->reportName, true);
        } catch (\PrestaScan\Exception\UnauthenticatedException $unauthException) {
            self::dieWithError($this->module->l('You might be not logged in. Please refresh the page and check your connexion.', 'AdminPrestascanSecurityReportsController'));
        } catch (\PrestaScan\Exception\JobAlreadyInProgressException $jobAlreadyInProgressException) {
            self::dieWithError($this->module->l('A scan is already in progress. You will be notified once completed.', 'AdminPrestascanSecurityReportsController'));
        } catch (\PrestaScan\Exception\TooManyAttempsException $exp) {
            // Rate limit
            self::dieWithError($this->module->l('You have reached the limit of number of attemps allowed for this scan today. Please try again in 24 hours.', 'AdminPrestascanSecurityReportsController'));
        } catch (\Exception $exeption) {
            self::dieWithError($this->module->l('Error while generating this report. Please try again.', 'AdminPrestascanSecurityReportsController'));
        }

        if (\PrestaScanQueue::isJobAlreadyCompleted($report->reportName)) {
            $report->deleteReportCompleted();
        }

        if (!$bath) {
            $forceActiveTab = array();
            switch ($classReport) {
                case \PrestaScan\Reports\VulnerableModulesReport::class:
                    $forceActiveTab['forceactivetab'] = 'modules_vulnerabilities';
                    break;
                case \PrestaScan\Reports\UnusedModulesReport::class:
                    $forceActiveTab['forceactivetab'] = 'modules_unused';
                    break;
                case \PrestaScan\Reports\DirectoriesProtectionReport::class:
                    $forceActiveTab['forceactivetab'] = 'report-files';
                    break;
                case \PrestaScan\Reports\CoreVulnerabilitiesReport::class:
                    $forceActiveTab['forceactivetab'] = 'report-core-vulnerabilities';
                    break;
                default:
                    $forceActiveTab = false;
                    break;
            }
            \PrestaScan\Tools::printAjaxResponse(true, false, '', $forceActiveTab);
        }
    }

    private static function dieWithError($error)
    {
        \PrestaScan\Tools::printAjaxResponse(false, true, $error);
    }

    public function ajaxProcessDismmissedAlert()
    {
        try {
            $alert = new \PrestaScanVulnAlerts((int) Tools::getValue('alert_id'));
            $alert->dismissAlert(Context::getContext()->employee->id);
        } catch (\Exception $e) {
            self::dieWithError($this->module->l('Error while dismissing the alert. Please try again.', 'AdminPrestascanSecurityReportsController'));
        }

        \PrestaScan\Tools::printAjaxResponse(true, false);
    }

    public function ajaxProcessCancelJobTooLong()
    {        
        try {
            $job = \PrestaScanQueue::getJobByActionNameAndState(Tools::getValue('type'), \PrestaScanQueue::$actionname['SUGGEST_CANCEL']);
            if (!isset($job['jobid'])) {
                self::dieWithError($this->module->l('Error while cancelling the scan. The scan does not exist.', 'AdminPrestascanSecurityReportsController'));
            }

            $jobId = $job['jobid'];
            $payload = \PrestaScan\Api\Queue::delete($job['jobid']);
            if (isset($payload['error'])
                    && isset($payload['error']['code'])
                    && (int) $payload['error']['code'] === 204
                ) {
                \PrestaScanQueue::updateJob($job['jobid'], \PrestaScanQueue::$actionname['CANCEL'], $this->module->l('Manually cancelled by user', 'AdminPrestascanSecurityReportsController'));
                $this->setScanStatus($job['action_name'], false);
                \PrestaScan\Tools::printAjaxResponse(true, false, $this->module->l('The job has been cancelled successfully, please reload the page.', 'AdminPrestascanSecurityReportsController'));
            }

            $this->checkPayloadForErrorAndDie($job, $jobId, $payload);
            if (is_array($payload)) {
                $this->saveReport($job, $jobId, $payload);
                $scanCompleted = true;
                \PrestaScan\Tools::printAjaxResponse(true, false, $this->module->l('We were able to retrieve your scan ! Please reload this page to view the report.', 'AdminPrestascanSecurityReportsController'));
            }
        } catch (\Exception $e) {
            self::dieWithError($this->module->l('Error while cancelling the scan. Please try again.', 'AdminPrestascanSecurityReportsController'));
        }

        // Fall back
        \PrestaScan\Tools::printAjaxResponse(true, false);
    }

    private function checkJobTooLongProgress()
    {
        $timeLimit = \PrestaScan\Tools::getTimeTooLongParameter();
        $jobsLong = \PrestaScanQueue::checkJobsRunningForTooLong($timeLimit);
        if (is_array($jobsLong) && count($jobsLong)) {
            foreach ($jobsLong as $job) {
                $stateMessage = $this->module->l('The scan is running for a while now.', 'AdminPrestascanSecurityReportsController');
                \PrestaScanQueue::updateJob($job['jobid'], \PrestaScanQueue::$actionname['SUGGEST_CANCEL'], $stateMessage);
                $this->setScanStatus($job['action_name'], 'suggest_cancel');
            }
            $errorMessage = $this->module->l('One of your scans appears to be taking longer than expected. Please refresh this page to obtain updated information.', 'AdminPrestascanSecurityReportsController');
            \PrestaScan\Tools::printAjaxResponse(true, false, $errorMessage);
        }
    }

    public function ajaxProcessRefreshModuleStatus()
    {
        Configuration::deleteByName('PRESTASCAN_UPDATE_VERSION_AVAILABLE');
        Configuration::deleteByName('PRESTASCAN_LAST_VERSION_CHECK');

        \PrestaScan\Tools::printAjaxResponse(true, false);
    }
}
