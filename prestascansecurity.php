<?php
/*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact <security@prestascan.com>
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prestascansecurity extends Module
{
    public function __construct()
    {
        $this->name = 'prestascansecurity';
        $this->tab = 'others';
        $this->version = '0.8.8';
        $this->author = 'PrestaScan';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaScan Security');
        $this->description = $this->l('Scan your PrestaShop website to identify malwares and known vulnerabilities in PrestaShop core and modules');

        $this->confirmUninstall = $this->l('Are you sure to uninstall this module?');

        require_once __DIR__."/vendor/autoload.php";
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->createTabs() ||
            !$this->generateModuleHash() ||
            !$this->installDb() ||
            !$this->registerHook('dashboardZoneOne')
        ){
            return false;
        }
        Configuration::updateGlobalValue(
            'PRESTASCAN_WEBCRON_TOKEN',
            \PrestaScan\Tools::getHashByName("webcron", Configuration::get('PRESTASCAN_SEC_HASH'))
        );
        $this->installAlertBox();

        return true;
    }

    /**
     * Install the database table(s) for this project
     *
     * We retrieve the sql instructions from sql_install.php
     *
     */
    public function installDb()
    {
        $sql = array();
        $return = true;
        include(dirname(__FILE__).'/sql_install.php');
        if (empty($sql)) {
            return true;
        }
        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }
        return $return;
    }

    public function uninstallDb()
    {
        $sql = array();
        include(dirname(__FILE__).'/sql_install.php');
        foreach (array_keys($sql) as $name) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS '.$name);
        }

        return true;
    }

    public function uninstall()
    {
        //Delete all configurations and cache files
        \PrestaScan\Tools::resetModuleConfigurationAndCache(true);

        return parent::uninstall() && $this->removeTabs() && $this->uninstallDb();
    }

    /**
     * Install an alert box.
     *
     * Add an a section that will be visible in the dashbord of the shop.
     * The section will be moved at the top of the dashboard to display security alerts.
     *
     */
    protected function installAlertBox()
    {
        // Add a alert box in the dashboard
        $dashboardZoneOneHook = Hook::getIdByName('dashboardZoneOne');
        $positions = $this->getPositionsDashboardZoneOne($dashboardZoneOneHook);
        if (!empty($positions) && count($positions) > 0) {
            // Increase the position of all other modules hooked in the dashboard
            foreach ($positions as $module => $position) {
                if ($module != $this->id) {
                    $this->updatePositionHookDashboardZoneOne($dashboardZoneOneHook, $module, $position + 1);
                }
            }
            // Move our module at the first position
            $this->updatePositionHookDashboardZoneOne($dashboardZoneOneHook, $this->id, 1);
        }
    }

    /**
     * Retrieve the positions of existing modules in the dashboard hook.
     *
     * @param int $idHook The ID of our DashBoard Hook.
     * @return array List of all modules and positions hooked in the DashBoard.
     */
    protected function getPositionsDashboardZoneOne($idHook)
    {
        $positions = array();

        $result = Db::getInstance()->executeS('
            SELECT `hm`.`id_module`, `hm`.`position`
            FROM `'._DB_PREFIX_.'hook_module` hm
            WHERE `hm`.`id_hook` = '.(int)$idHook.'
            ORDER BY `hm`.`position`
        ');

        if ($result) {
            foreach ($result as $row) {
                $positions[$row['id_module']] = (int)$row['position'];
            }
        }

        return $positions;
    }

    /**
     * Update the position of a module for the DashBoard hook
     *
     * @param int $idHook The ID of our DashBoard Hook.
     * @param int $idModule The ID of the module to update.
     * @param int $position The new position
     * @return bool
     */
    protected function updatePositionHookDashboardZoneOne($idHook, $idModule, $position)
    {
        return Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'hook_module`
            SET `position` = '.(int)$position.'
            WHERE `id_hook` = '.(int)$idHook.'
            AND `id_module` = '.(int)$idModule
        );
    }

    protected function createTabs()
    {
        // In PS 1.5, you'll have a fatal error. You need to manually add a Menu (in Adminsitration > Menu), with the following details :
        // Name : AdminPrestascanSecurityReports
        // Classe : AdminPrestascanSecurityReports
        // Module : PrestascanSecurity
        // Active : No

        $tabs = array(
            'AdminPrestascanSecurityReports',
            'AdminPrestascanSecurityFileviewer',
        );

        $result = true;
        foreach ($tabs as $tabName) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $tabName;
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('Ajax PrestaScan Security');
            }
            $tab->id_parent = -1;
            $tab->module = $this->name;
            $result = $result ? (bool)$tab->add() : $result;
        }

        return $result;
    }

    protected function removeTabs()
    {
        $tabs = array(
            'AdminPrestascanSecurityReports',
            'AdminPrestascanSecurityFileviewer',
        );

        foreach ($tabs as $tabName) {
            if ($tab_id = (int)Tab::getIdFromClassName($tabName)) {
                $tab = new Tab($tab_id);
                $tab->delete();
            }
        }

        return true;
    }

    public function hookDashboardZoneOne()
    {
        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $updateAvailable = Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE') ? true : false;
        $alerts = $vulnAlertHandler->getNewVulnerabilityAlerts();

        $this->context->smarty->assign("module_upgrade_available", $updateAvailable);
        $this->context->smarty->assign("module_link", $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        $this->context->smarty->assign('alert_modules_vulnerability', $alerts);

        if ($updateAvailable || $alerts) {
            $this->context->controller->addCSS($this->_path.'views/css/dashboard.css');
            return $this->display(__FILE__, 'dashboard_zone_two.tpl');
        } else {
            return;
        }
    }

    public function generateModuleHash()
    {
        // This hash is generated during the initial setup of the module
        // It will later be combined with the Cookie Key to avoid guessable log path
        // and to provide a token to communication with the OAuth2 FrontController from the BackOffice

        if (version_compare(phpversion(), '7.0.0', '<')) {
            // < PHP 7
            // random_bytes was introduced in PHP 7
            $randomHash = substr(md5(microtime()),rand(0,26),10);
        } else {
            $randomHash = bin2hex(random_bytes(18));
        }
        Configuration::updateGlobalValue('PRESTASCAN_SEC_HASH', $randomHash);

        return true;
    }

    public function getContent()
    {
        $dummyData = Tools::getValue("dummy") ? true : false;

        // @todo : Check if used, otherwise delte it.
        // We create a hash for the log files (which will be different from the hash for the FC token)
        // So in case the cache file hash is known, the hash for the FC will remain unknown.
        //$recursiveScanDirectoryHash = \PrestaScan\Tools::getHashByName("recursiveScanDirectory", $moduleHash);
        // log of directories scanned for malware
        /*if (file_exists(_PS_MODULE_DIR_.'prestascansecurity/logs/scan_recursiveScanDirectory_'.$recursiveScanDirectoryHash.'.log')) {
            $this->context->smarty->assign('recursiveScanDirectoryLog',
                $this->_path.'logs/scan_recursiveScanDirectory_'.$recursiveScanDirectoryHash.'.log');
        }*/
        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $moduleNewVulnerabilitiesAlert = $vulnAlertHandler->getNewVulnerabilityAlerts();

        $this->includeAdminResources($moduleNewVulnerabilitiesAlert);
        $this->assignAdminVariables($dummyData, $moduleNewVulnerabilitiesAlert);
        $this->displayInitialScanAndScanProgress($dummyData);

        // Check if user is connected
        $isLogged = false;
        try {
            $OAuth = new \PrestaScan\OAuth2\Oauth();
            $isLogged = $OAuth->getAccessTokenObj();
        } catch (Exception $exp) {
            if (!Tools::getValue('localoauth')) {
                // Local oauth is not supported, but we do not display an error.
                $this->context->smarty->assign('prestascansecurity_isLoggedIn_error', $this->l('An error occured while connecting you to the server.'));
            } else {
                // We simulate the login (note that this is NOT a secuirty issue, tokens are not validated)
                $isLogged = true;
            }
        } finally {

            if ($dummyData) {
                $isLogged = true;
            }

            $this->context->smarty->assign('prestascansecurity_isLoggedIn', $isLogged);
        }

        // check if module update is available
        if($isLogged && !$dummyData) {
            $updateObj = new \PrestaScan\Update($this->context, $this);
            $updateObj->checkForModuleUpdate();
            $updateAvailable = Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE') ? true : false;
            $this->context->smarty->assign("module_upgrade_available", $updateAvailable);

            // check if banner is available
            $bannerResponse = \PrestaScan\Banner::getBanner();
            if (!empty($bannerResponse)) {
                $this->context->smarty->assign('banner', $bannerResponse);
            }
        }

        return $this->display(__FILE__, 'views/templates/admin/layouts/main.tpl');
    }

    protected function displayInitialScanAndScanProgress($dummyData)
    {
        $displayInitialScan = true;
        $completedJobs = \PrestaScanQueue::getJobsByState(\PrestaScanQueue::$actionname['COMPLETED']);
        if (!empty($completedJobs)) {
            $displayInitialScan = false;
        }
        $progressScans = Configuration::get('PRESTASCAN_SCAN_PROGRESS');
        if (!empty($progressScans)) {
            $progressScans = json_decode($progressScans, true);
            foreach($progressScans as $scan) {
                if ($scan) {
                    $displayInitialScan = false;
                    break;
                }
            }
        }

        $displayInitialScan = $dummyData ? false : $displayInitialScan;

        $this->context->smarty->assign('displayInitialScan', $displayInitialScan);
        $this->context->smarty->assign('progressScans', $progressScans);
    }

    protected function assignAdminVariables($dummyData, $moduleNewVulnerabilitiesAlert)
    {
        $this->assignReportVariables();
        $this->assignSmartyStaticVariables();
        $this->assignSettingsPageUrl();
        $this->assignTokenAndShopUrlVariables();

        if ($dummyData) {
            $this->assignDummyDataVariables();
        }

        $this->context->smarty->assign('alert_new_modules_vulnerability', $moduleNewVulnerabilitiesAlert);
    }

    protected function assignReportVariables()
    {
        // Load the reports
        $reports = new \PrestaScan\Reports\Report();
        foreach ($reports->getReports() as $reportName => $cacheFile) {
            $this->smartyAssignReportVariables($cacheFile, $reportName);
        }
    }

    protected function assignSmartyStaticVariables()
    {
        $this->context->smarty->assign([
            'scanpath' => _PS_ROOT_DIR_,
            'prestascansecurity_reports_ajax' => $this->context->link->getAdminLink('AdminPrestascanSecurityReports'),
            'prestascansecurity_fileviewer_ajax' => $this->context->link->getAdminLink('AdminPrestascanSecurityFileviewer'),
            'prestascansecurity_tpl_path' => _PS_MODULE_DIR_ . 'prestascansecurity/views/templates/admin/',
        ]);
    }

    protected function assignSettingsPageUrl()
    {
        $settings_page_url = 'https://security.prestascan.com/user/profile';
        if (\Configuration::get('PRESTASCAN_TEST_MODE_OAUTH')) {
            $settings_page_url = "http://127.0.0.1/user/profile";
        }
        $this->context->smarty->assign('settings_page_url', $settings_page_url);
    }

    protected function assignDummyDataVariables()
    {
        $this->context->smarty->assign([
            'modules_unused_results' => \PrestaScan\DemoData::unusedModulesData(),
            'modules_vulnerabilities_results' => \PrestaScan\DemoData::vulnerableModulesData(),
            'directories_listing_results' => \PrestaScan\DemoData::unprotectedDirectories(),
            'core_vulnerabilities_results' => \PrestaScan\DemoData::coreVulnerabilitiesDisplayReport(),
            'files_status' => 'high',
            'files_last_scan_date' => '30 Novembre 2022 à 16h49',
            'modules_status' => 'medium',
            'modules_last_scan_date' => '30 Novembre 2022 à 16h49',
            'vulnerabilities_status' => 'outdated',
            'vulnerabilities_last_scan_date' => '30 Novembre 2022 à 16h49',
            'directories_listing_status' => 'high',
            'directories_listing_last_scan_date' => '30 Novembre 2022 à 16h49',
            'core_vulnerabilities_status' => 'medium',
            'core_vulnerabilities_last_scan_date' => '30 Novembre 2022 à 16h49',
            'modules_vulnerabilities_status' => 'outdated',
            'modules_vulnerabilities_last_scan_date' => '30 Novembre 2022 à 16h49',
        ]);
    }

    protected function assignTokenAndShopUrlVariables()
    {
        // Token used to communicate with the OAuth2 FrontController
        $moduleHash = Configuration::get('PRESTASCAN_SEC_HASH');
        $tokenFC = \PrestaScan\Tools::getHashByName("FCOauth", $moduleHash);
        $this->context->smarty->assign([
            'prestascansecurity_tokenfc' => $tokenFC,
            'prestascansecurity_shopurl' => \PrestaScan\Tools::getShopUrl(),
            'prestascansecurity_e_firstname' => Context::getContext()->employee->firstname,
            'prestascansecurity_e_lastname' => Context::getContext()->employee->lastname,
            'prestascansecurity_e_email' => $this->context->employee->email,
            // For localhost development (see Oauth)
            'prestascansecurity_localoauth' => Tools::getValue('localoauth') ? 1 : 0,
            'webcron_token' => Configuration::get('PRESTASCAN_WEBCRON_TOKEN'),
            'ps_shop_urls' => implode(";", array_map('urlencode', $this->getShopUrls())),
        ]);
    }

    protected function getShopUrls()
    {
        // Retrieve the list of shop urls
        // We will need to send those to the the server during the registration process
        $shopUrls = [];
        $http = Tools::usingSecureMode() ? "https://" : "http://";
        foreach (Shop::getShops(true) as $shopId) {
            $shop = new Shop($shopId["id_shop"]);
            foreach ($shop->getUrls() as $u) {
                $shopUrls[] = $http . $u["domain_ssl"] . $u["physical_uri"] . $u["virtual_uri"];
            }
        }
        return $shopUrls;
    }

    protected function includeAdminResources($moduleNewVulnerabilitiesAlert)
    {
        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $mediaJsDef = array(
            'question_to_this_action' => $this->l('Are you sure to this action ?'),
            'question_to_logout' => $this->l('Are you sure to log out ?'),
            'js_error_occured' => $this->l('An error occured while generating the report. This may be due to a timeout, and you may try to apply filters to your search to reduce processing time.'),
            'question_to_logout' => $this->l('Are you sure to log out ?'),
            'js_description' => $this->l('Description'),
            'text_confirm_log_me_out' => $this->l('Yes, log me out'),
            'text_reload' => $this->l('Click here to refresh the page'),
            'text_yes' => $this->l('Yes'),
            'text_cancel' => $this->l('Cancel'),
            'text_yes_dismiss' => $this->l('dismiss'),
            'question_to_this_dismiss_action' => $this->l('You are about to remove this alert. You will need to redo a scan to get additional details. Are you sure to dismiss this alert? '),
            'banner_vulnerability_more_action' => $this->l('This alert is triggered because a new vulnerability was discovered in PrestaShop for this module. Your shop may be vulnerable if the module is not patched yet. Please contact your agency or our team of experts to fix the issue. Please redo a full scan of your module to get more details about the vulnerability.'),
            'banner_vulnerability_more_details'  => $this->l('More details about this issue:'),
            'alert_new_modules_vulnerability' => !empty($moduleNewVulnerabilitiesAlert) ? true : false,
        );

        //Check cookie if update module is running
        $isModuleUpdated = Context::getContext()->cookie->__get('psscan_module_updated');
        if ($isModuleUpdated == true) {
            Context::getContext()->cookie->__unset('psscan_module_updated');
            $mediaJsDef['module_updated_confirmation_message'] = $this->l('Your module has been successfully updated.');
        }

        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            Media::addJsDef($mediaJsDef);
        } else {
            $this->context->smarty->assign('mediaJsDef', $mediaJsDef);
        }
        
        $jsFiles = [
            'views/js/reports.js',
            'views/js/authentication.js',
            'views/js/datatables.1.10.25.js',
            'views/js/dataTables.buttons.min.js',
            'views/js/file-size.js',
            'views/js/buttons.html5.min.js',
            'views/js/buttons.print.min.js',
            'views/js/jquery-ui.min.js'
        ];

        $cssFiles = [
            'views/css/admin.css',
            'views/css/datatables.1.10.25.css',
            'views/css/buttons.dataTables.min.css',
            'views/css/jquery-ui.min.css',
            'views/css/jquery-ui.structure.min.css',
            'views/css/jquery-ui.theme.min.css'
        ];

        foreach ($jsFiles as $jsFile) {
            $this->context->controller->addJS($this->_path . $jsFile);
        }

        foreach ($cssFiles as $cssFile) {
            $this->context->controller->addCSS($this->_path . $cssFile);
        }

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        } else {
            $this->context->controller->addCSS($this->_path.'views/css/admin_1.5.css');
        }
    }

    protected function smartyAssignFiltersVariables($filters, $prefix)
    {
        foreach ($filters as $filterKey => $filterValue) {
            if ($filterKey == "exclusionList") {
                $filterValue = !empty($filterValue) ? implode("\r\n", $filterValue) : "/exemple_to_exclude/file.php\r\n/exemple_to_exclude/directory/";
            }
            $this->context->smarty->assign($prefix."_".$filterKey, $filterValue);
        }
    }

    protected function smartyAssignReportVariables($reportCacheFile, $reportName)
    {
        // We create a hash for the cache files (to avoid direct access by guessing the name of the file)
        $prefix = $reportName;
        $report = [];
        if (is_file($reportCacheFile)) {
            $report = unserialize(file_get_contents($reportCacheFile));
            if (isset($report['error']) && $report['error'] !== false) {
                $this->context->smarty->assign($prefix . '_error', $report['error']);
            } else {
                $this->context->smarty->assign($prefix . '_results', $report['report']['results']);
            }
            $this->context->smarty->assign($prefix . '_date_report', date('F d, Y \a\t H:i', $report['date_report']));
            if (!empty($report['report']['filters'])) {
                $this->smartyAssignFiltersVariables($report['report']['filters'], $prefix);
            }
        } else {
            $this->context->smarty->assign($prefix . '_results', false);
        }
    }

    /**
     * Retrieve the translated vulnerability name
     *
     * This function has been place here instead of in an utility class un oder to be
     * able to use the translation system.
     */
    public function getVulnerabilityExtendedNameTranslated($shortName) {
        $vulnerabilityTypes = array(
            'xss' => $this->l('Cross-Site Scripting (XSS)'),
            'sql_injection' => $this->l('SQL Injection'),
            'code_injection' => $this->l('Code Injection'),
            'xss_stored' => $this->l('Stored Cross-Site Scripting (XSS)'),
            'spam' => $this->l('Spam'),
            'data_breach' => $this->l('Data Breach'),
            'improper_access_control' => $this->l('Improper Access Control'),
            'unknown' => $this->l('Unknown Vulnerability'),
            'data_deletion' => $this->l('Data Deletion'),
            'unsecure_token' => $this->l('Unsecure Token'),
            'path_traversal' => $this->l('Path Traversal'),
            'token_bypass' => $this->l('Token Bypass'),
            'classification_missing' => $this->l('Classification Missing'),
            // Add more vulnerability types here if needed
        );

        if (array_key_exists($shortName, $vulnerabilityTypes)) {
            return $vulnerabilityTypes[$shortName];
        }

        return ucfirst($shortName); // Return the input value if the short name is not found
    }

    /**
     * Versions of prestashop 1.6, don't support namespaces in Smarty templates
     * 
     * This function is designed to bypass this limitation, by moving the namespaced call on the PHP side
     */
    public static function redirectTools($functionName, $param)
    {
        return PrestaScan\Tools::{$functionName}($param);
    }
}
