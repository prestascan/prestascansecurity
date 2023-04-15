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

use Module;
use PrestaScan\Exception\UpdateException;

class Update extends Module
{
    protected $context;
    protected $module;

    public function __construct($context, $moduleInstance)
    {
        $this->context = $context;
        $this->module = $moduleInstance;
    }

    /*
    * This function checks if an update for the module is available.
    * If this is the case, this shows the flash message with a link to update the module
    */
    public function checkForModuleUpdate()
    {
        $updateAvailable = \Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE');

        // Si la date de dernière verification de mis à jour est de plus d'une heure
        $checkMoreThanOneHour = $this->checkUpdateDateExpired(3600);

        if (!$updateAvailable || $checkMoreThanOneHour) {
            // Si il n'y a pas d'update en attente, on verifie si il y a une nouvelle version sur le serveur
            $lastCheck = \Configuration::get('PRESTASCAN_LAST_VERSION_CHECK');
            if (!$lastCheck || $checkMoreThanOneHour) {
                $resultApi = false;
                try {
                    $resultApi = self::checkForUpdateVersion($this->module->version);
                } catch (\Exception $ex) {
                    // API error, do not trigger a new exception, we can continue.
                }
                $this->configurationUpdate($resultApi);
            }
        }
    }

    /*
    * This function downloads the latest version of the module from the server and updates the local version
    */
    public function ajaxProcessUpdateModule()
    {
        try {
            $response = self::checkForUpdateVersion($this->module->version);
            if (!isset($response['url'])) {
                if (isset($response['success']) && $response['success'] === false
                    && isset($response['error']) && isset($response['error']['code']) && $response['error']['code'] === 200) {
                    $error = $this->module->l('Your module is already up to date. Please reload this page.');
                    \Configuration::updateGlobalValue('PRESTASCAN_LAST_VERSION_CHECK', (new \DateTime())->format('Y-m-d H:i:s'));
                    \Configuration::updateGlobalValue('PRESTASCAN_UPDATE_VERSION_AVAILABLE', false);
                    throw new UpdateException($error);
                }
                $error = $this->module->l('Error fetching the new version. Please try again.');
                throw new UpdateException($error);
            }
            $url = $response['url'];
            if ($this->downloadAndExtractZipModuleFile($url)) {
                $module = \Module::getInstanceByName($this->module->name);
                $newVersion = $this->getModuleVersionFromDisk();
                if (!empty($newVersion)) {
                    // PrestaShop Core is sometimes returning warnings during the update. We do not have control over this code.
                    // So we are using the Error Suppression Operator to hide the warnings/notice calleing this function.
                    @ $module->runUpgradeModule();
                    \Module::upgradeModuleVersion($this->module->name, $newVersion);
                    \Configuration::updateGlobalValue('PRESTASCAN_LAST_VERSION_CHECK', (new \DateTime())->format('Y-m-d H:i:s'));
                    \Configuration::updateGlobalValue('PRESTASCAN_UPDATE_VERSION_AVAILABLE', false);
                    return true;
                } else {
                    $error = $this->module->l('Error trying to install the new module. Please try updating the module from your module list.');
                    throw new UpdateException($error);
                }
            } else {
                $error = $this->module->l('Unable to download and extract module!');
                throw new UpdateException($error);
            }
        } catch (UpdateException $updateException) {
            // Return to the Controller
            throw $updateException;
        } catch (\Exception $e) {
            // Other type of exception.
            $error = $this->module->l('Unexcepted error trying to retrieve the new module. Reload the page and try again.');
            throw new \Exception($error);
        }
    }

    private function checkUpdateDateExpired($secondsExpire = 3600)
    {
        $currentDate = (new \DateTime())->format('Y-m-d H:i:s');
        $lastCheck = \Configuration::get('PRESTASCAN_LAST_VERSION_CHECK');
        return ((strtotime($currentDate) - strtotime($lastCheck)) / ($secondsExpire)) > 1;
    }

    private function configurationUpdate($response)
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        \Configuration::updateGlobalValue('PRESTASCAN_LAST_VERSION_CHECK', $now);

        $updateVersionAvailable = (is_array($response) && isset($response['url'])) ? true : false;
        \Configuration::updateGlobalValue('PRESTASCAN_UPDATE_VERSION_AVAILABLE', $updateVersionAvailable);
    }

    private function downloadAndExtractZipModuleFile($url)
    {
        try {
            $zipFile = $this->module->name . '.zip';
            $extractDir = _PS_ROOT_DIR_ . '/modules/';
            $zipResource = fopen($zipFile, 'w');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FILE, $zipResource);

            $page = curl_exec($ch);
            if (!$page) {
                $error = $this->module->l('Cannot access to the module URL.<br />Try retriving it manually: <a href="%s">%s</a>', [$url, $url], 'Modules.prestascansecurity.Alert');
                throw new UpdateException($error);
            }
            curl_close($ch);

            /* Open the Zip file */
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) !== true) {
                $error = $this->module->l('Malformated module archive. Please try again.');
                throw new UpdateException($error);
            }
            /* Extract Zip File */
            $zip->extractTo($extractDir);
            $zip->close();

            return true;
        } catch (UpdateException $updateException) {
            throw $updateException;
        } catch (\Exception $ex) {
            $error = $this->module->l('Unexpected error downloading or extracting module archive.');
            throw new UpdateException($error);
        }
    }

    private function getModuleVersionFromDisk()
    {
        try {
            $file = trim(file_get_contents(_PS_MODULE_DIR_ . $this->module->name . '/' . $this->module->name . '.php'));
            preg_match("/(version)\s=\s+([0-9\.'])+/i", $file, $matches);
            if ($matches[0]) {
                return trim(str_replace(array("version", "=", "'"), array("", "", ""), $matches[0]));
            }
        } catch (\Exception $e) {
            $error = $this->module->l('Error retrieve module version.', [], 'Modules.prestascansecurity.Alert');
            throw new UpdateException($error);
        }
        $error = $this->module->l('Invalid version data', [], 'Modules.prestascansecurity.Alert');
        throw new UpdateException($error);
    }

    /*
    * API Call to check if an update is available for the module.
    * Will return the URL of the zip of the latest version to upgrade.
    */
    private static function checkForUpdateVersion($version)
    {
        $reponse = false;
        try {
            $request = new \PrestaScan\Api\Request(
                'prestascan-api/v1/check-version/' . $version
            );
            $reponse = $request->getResponse();
        } catch (\Exception $ex) {
            $reponse = [
                'error' => true,
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
            ];
        }

        return $reponse;
    }
}
