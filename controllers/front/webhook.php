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

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestascansecurityWebhookModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        try {
            // 1. check message signature using shared secret (token)
            // to make sure message has not been tampered
            // and validate origin of the message
            if (!$this->validateSignature()) {
                $this->returnServer(401, 'Unauthorized');
            }

            parent::init();

            $rawData = file_get_contents('php://input');
            $postedData = json_decode($rawData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->returnServer(500, 'Invalid payload format');
            }

            $result = null;

            // 2. process event
            switch ($postedData['event']) {
                case 'ping':
                    $this->pingAction($postedData);
                    break;
                case 'scan_completed':
                    $this->scanCompleted($postedData);
                    break;
                case 'alert_module':
                case 'alert_module_no_detail':
                    $this->alertModuleVulnerable($postedData);
                    break;
                case 'automatic_scan':
                    $this->automationScan($postedData);
                    break;
                case 'alert_core':
                case 'alert_core_no_detail':
                    $this->alertCoreVulnerable($postedData);
                    break;
                default:
                    $this->returnServer(400, 'Invalid Event');
                    break;
            }

            // 3. post result back to server via secured channel with Oauth2
            // As much as possible, webhook should output only status messages and not full results
            if (isset($postedData['return_id']) && $postedData['return_id']) {
                if (!$result) {
                    $this->returnServer(200, 'OK - No result');
                }
                $body = array(
                    'return_id' => $postedData['return_id'],
                    'job_id'    => $postedData['job_id'],
                    'result'    => $result,
                );

                // send $body to server at endpoint : /webcron/result
                $request = new \PrestaScan\Api\Request(
                    'prestascan-api/v1/webcron/result',
                    'POST',
                    $body
                );
                $response = $request->getResponse();

                if (isset($response['completed']) && $response['completed']) {
                    $this->returnServer(200, 'OK');
                } else {
                    $this->returnServer(400, 'Error sending result back: ' . $response['error']['code']
                         . ' - ' . $response['error']['message']);
                }
            }
        } catch (\Exception $e) {
            $return = [
                'message_error' => 'Error processing webhook',
                'exception_message' => $e->getMessage(),
            ];
            $this->returnServer(500, $return);
        }

        $this->returnServer(501, 'Unknown');
    }

    private function pingAction($postedData)
    {
        // Required for registration of webhooks
        $this->returnServer(200, 'OK');
    }

    private function scanCompleted($postedData)
    {        
        if (isset($postedData['error']) && !empty($postedData['error'])) {
            $payload = $postedData['payload'];
            $jobId = $payload['jobId'];

            $this->checkJobStateBeforeProcess($jobId);
            // We inidicate that we now need to retrieve the data from the API
            \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['TORETRIEVE'], $postedData['error']);

            // And then return success for the webhook
            $this->returnServer(200, 'OK');
        }

        if (!isset($postedData['payload'])
            || !isset($postedData['payload']['jobId'])) {
            $this->returnServer(500, 'Invalid webhook parameters');
        }

        $payload = $postedData['payload'];
        $jobId = $payload['jobId'];

        // We inidicate that we now need to retrieve the data from the API
        $this->checkJobStateBeforeProcess($jobId);
        \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['TORETRIEVE']);
        $this->returnServer(200, 'OK');
    }

    private function checkJobStateBeforeProcess($jobId)
    {
        $job = \PrestaScanQueue::getJobsByJobid($jobId);
        if (empty($job)) {
            // The job doesn't exists in the table. No need to return an error.
            $this->returnServer(500, 'Job no longer existing');
        }
        if ($job['state'] === \PrestaScanQueue::$actionname['COMPLETED']
            || $job['state'] === \PrestaScanQueue::$actionname['CANCEL']
            || $job['state'] === \PrestaScanQueue::$actionname['ERROR']
        ) {
            // The Job exists but is already processed
            $this->returnServer(500, 'Job already processed: '.$job['state']);
        }
    }

    private function alertModuleVulnerable($postedData)
    {
        $vulnerabilityHandler = new \PrestaScan\VulnerabilityAlertHandler($this->module);
        $this->returnServer(200, json_encode($vulnerabilityHandler->handle($postedData['message'])));
    }

    private function automationScan($postedData)
    {
        $AutomationScanHandler = new \PrestaScan\AutomationScanHandler($this->module);
        $return = $AutomationScanHandler->handle($postedData);
        $this->returnServer(200, 'OK');
    }

    private function alertCoreVulnerable($postedData)
    {
        $coreVulnerabilityHandler = new \PrestaScan\CoreVulnerabilityAlertHandler($this->module);
        $this->returnServer(200, json_encode($coreVulnerabilityHandler->handle($postedData['message'])));
    }

    protected function returnServer($httpCode, $message)
    {
        $body = [
                    'success' => false,
                    'error' => [
                        'code' => $httpCode,
                        'message' => $message,
                    ],
                    'payload' => [
                        'code' => $httpCode,
                        'message' => $message,
                    ]
                ];
        if ($this->isJson($message)) {
            $data = json_decode($message, true);
            $body['payload']['message'] = '';
            $body['payload'] += $data;
        }
        if ($httpCode == 200) {
            $body['success'] = true;
            unset($body['error']);
        } else {
            unset($body['payload']);
        }
        http_response_code($httpCode);
        print json_encode($body);
        exit();
    }

    /**
     * Verify message Signature using token
     * @return bool
     */
    private function validateSignature()
    {
        $tokenConfig = Configuration::get('PRESTASCAN_WEBCRON_TOKEN');
        if (!$tokenConfig || empty($tokenConfig)) {
            // Fallback function in case that the token config is not set
            // Module not installed properly ? Or other issue with configuration table.
            return false;
        }

        $rawPostData = file_get_contents('php://input');
        $signature = hash_hmac('sha256', $rawPostData, $tokenConfig);

        // Check if getallheaders function exists for this PHP version
        // https://github.com/php/php-src/pull/3363
        if (!function_exists('getallheaders')) {
            $headers = $this->getallheadersFallback();
        } else {
            $headers = getallheaders();
        }

        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'signature') {
                // Does the signature match ? If yes, success
                return hash_equals($value, $signature);
            }
        }

        // Signature is not matching, security issue
        return false;
    }

    private function getallheadersFallback()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    private function isJson($value)
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            $data = json_decode($value, true);
            if (is_null($data)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }
}
