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
                    $this->alertModuleVulnerable($postedData);
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
                    'result'    => $result
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
                    $this->returnServer(400, 'Error sending result back : ' . $response['error']['code']
                         . ' - ' . $response['error']['message']);
                }
            }
        } catch (\Exception $e) {
            $this->returnServer(500, 'Error processing webhook');
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
        try {
            if (isset($postedData["error"])) {
                // @todo : There is an error with one of the report. What should we do?
                // update databas

                // And then return success for the webhook
                $this->returnServer(200, 'OK');
            }

            if (!isset($postedData["payload"])
                || !isset($postedData["payload"]['jobId'])) {
                $this->returnServer(500, 'Invalid webhook parameters');
            }

            $payload = $postedData["payload"];
            $jobId = $payload['jobId'];

            // We inidicate that we now need to retrieve the data from the API
            \PrestaScanQueue::updateJob($jobId, \PrestaScanQueue::$actionname['TORETRIEVE']);

        } catch (\Exception $e) {
            $this->returnServer(500, 'Error processing webhook');
        }

        $this->returnServer(200, 'OK');
    }

    private function scanFileCoreChangesAction($postedData)
    {
        // @todo
    }

    private function scanFileAddedModifiedAction($postedData)
    {
        // @todo
    }

    private function scanFileSuspiciousAction($postedData)
    {
        // @todo
    }

    private function alertModuleVulnerable($postedData)
    {
        $vulnerabilityHandler = new \PrestaScan\VulnerabilityAlertHandler($this->module);
        $this->returnServer(200, json_encode($vulnerabilityHandler->handle($postedData['message'])));
    }

    protected function returnServer($httpCode, $message)
    {
        http_response_code($httpCode);
        print $message;
        exit();
    }

    /**
     * Verify message Signature using token
     * @return bool
     */
    private function validateSignature()
    {
        if (!Configuration::get("PRESTASCAN_WEBCRON_TOKEN")) {
            return false;
        }
        
        $rawPostData = file_get_contents('php://input');
        $signature = hash_hmac('sha256', $rawPostData, Configuration::get("PRESTASCAN_WEBCRON_TOKEN"));
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'signature') {
                return hash_equals($value, $signature);
            }
        }

        // Signature not found.
        return false;
    }

}
