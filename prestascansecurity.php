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

class Prestascansecurity extends Module
{
    public $isLoggedIn = false;

    public function __construct()
    {
        $this->name = 'prestascansecurity';
        $this->tab = 'others';
        $this->version = '1.1.2';
        $this->author = 'PrestaScan';
        $this->need_instance = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaScan Security');
        $this->description = $this->l('Scan your PrestaShop website to identify malwares and known vulnerabilities in PrestaShop core and modules');

        $this->confirmUninstall = $this->l('Are you sure to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.5.0', 'max' => _PS_VERSION_];

        require_once __DIR__ . '/vendor/autoload.php';
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->createTabs() ||
            !$this->generateModuleHash() ||
            !$this->installDb() ||
            !$this->registerHook('dashboardZoneOne')) {
            return false;
        }
        Configuration::updateGlobalValue(
            'PRESTASCAN_WEBCRON_TOKEN',
            \PrestaScan\Tools::getHashByName('webcron', Configuration::get('PRESTASCAN_SEC_HASH'))
        );
        // Timeout, in minute, before suggesting job cancellation
        Configuration::updateGlobalValue('PRESTASCAN_SCAN_MAX_RUN_TIME', 5);
        // Update and Alert box in dashboard
        $this->installAlertBox();

        return true;
    }

    /**
     * Install the database table(s) for this project
     * We retrieve the sql instructions from sql_install.php
     */
    public function installDb()
    {
        $sql = array();
        $return = true;
        include \PrestaScan\Tools::getModulePath() . 'install/sql_install.php';
        if (empty($sql)) {
            return true;
        }
        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }

        // Flag to check if the upgrade was correctly run (to fix an issue when upgrade is done for versions > 1.0.3)
        \Configuration::updateGlobalValue('PRESTASCAN_FIX_1_0_4', true);

        return $return;
    }

    public function uninstallDb()
    {
        include \PrestaScan\Tools::getModulePath() . 'install/sql_install.php';
        foreach (array_keys($sql) as $name) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS ' . $name);
        }

        return true;
    }

    public function uninstall()
    {
        // Delete all configurations and cache files
        \PrestaScan\Tools::resetModuleConfigurationAndCache(true);

        return parent::uninstall() && $this->removeTabs() && $this->uninstallDb();
    }

    /**
     * Install an alert box.
     *
     * Add an a section that will be visible in the dashbord of the shop.
     * The section will be moved at the top of the dashboard to display security alerts.
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
     *
     * @return array List of all modules and positions hooked in the DashBoard.
     */
    protected function getPositionsDashboardZoneOne($idHook)
    {
        $positions = array();

        $result = Db::getInstance()->executeS('
            SELECT `hm`.`id_module`, `hm`.`position`
            FROM `' . _DB_PREFIX_ . 'hook_module` hm
            WHERE `hm`.`id_hook` = '.(int) $idHook .'
            ORDER BY `hm`.`position`
        ');

        if ($result) {
            foreach ($result as $row) {
                $positions[$row['id_module']] = (int) $row['position'];
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
     *
     * @return bool
     */
    protected function updatePositionHookDashboardZoneOne($idHook, $idModule, $position)
    {
        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'hook_module`
            SET `position` = ' . (int) $position . '
            WHERE `id_hook` = ' . (int) $idHook . '
            AND `id_module` = ' . (int) $idModule
        );
    }

    protected function createTabs()
    {
        $result = true;
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminPrestascanSecurityReports';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Ajax PrestaScan Security');
        }
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $tab->id_parent = 0;
        } else {
            $tab->id_parent = -1;
        }
        $tab->module = $this->name;

        return $result ? (bool) $tab->add() : $result;          
    }

    protected function removeTabs()
    {
        if ($tab_id = (int) Tab::getIdFromClassName('AdminPrestascanSecurityReports')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }

        return true;
    }

    public function hookDashboardZoneOne()
    {
        // Retrieve the alerts
        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $alerts = $vulnAlertHandler->getNewVulnerabilityAlerts();

        $updateAvailable = false;
        if ($this->isUserLoggedIn()) {
            // We check if updates are available
            $updateObj = new \PrestaScan\Update($this->context, $this);
            $updateObj->checkForModuleUpdate();
            $updateAvailable = Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE') ? true : false;
        }
        
        if (!$updateAvailable && !$alerts) {
            return;
        }

        $this->context->smarty->assign('module_upgrade_available', $updateAvailable);
        $link = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name .
            '&tab_module=' . $this->tab .
            '&module_name=' . $this->name .
            '&token=' . Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign('module_link', $link);
        $this->context->smarty->assign('alert_modules_vulnerability', $alerts);

        $this->context->controller->addCSS($this->_path . 'views/css/dashboard.css');
        return $this->display(__FILE__, 'dashboard_zone_two.tpl');
    }

    public function generateModuleHash()
    {
        // This hash is generated during the initial setup of the module
        // It will later be combined with the Cookie Key to avoid guessable log path
        // and to provide a token to communication with the OAuth2 FrontController from the BackOffice

        if (version_compare(phpversion(), '7.0.0', '<')) {
            // < PHP 7
            // random_bytes was introduced in PHP 7
            $randomHash = substr(md5(microtime()), rand(0,26), 10);
        } else {
            $randomHash = bin2hex(random_bytes(18));
        }
        Configuration::updateGlobalValue('PRESTASCAN_SEC_HASH', $randomHash);

        return true;
    }

    public function getContent()
    {
        // Update the module if requested to do so
        $this->updateModule();
        // Check for error message to display
        if ($error = $this->checkForErrorMessage()) {
            // @todo : Errors needs to be beautiful. So make a beautiful popup for a beautiful error <3
            return $error;
        }

        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $moduleNewVulnerabilitiesAlert = $vulnAlertHandler->getNewVulnerabilityAlerts();

        $this->includeAdminResources($moduleNewVulnerabilitiesAlert);
        $this->assignAdminVariables($moduleNewVulnerabilitiesAlert);
        $this->displayInitialScanAndScanProgress();

        // Check if user is connected
        $isLogged = $this->isUserLoggedIn();

        $this->context->smarty->assign('prestascansecurity_isLoggedIn', $isLogged);

        // check if module update is available
        if ($isLogged) {
            $updateObj = new \PrestaScan\Update($this->context, $this);
            $updateObj->checkForModuleUpdate();
            $updateAvailable = Configuration::get('PRESTASCAN_UPDATE_VERSION_AVAILABLE') ? true : false;
            $this->context->smarty->assign('module_upgrade_available', $updateAvailable);

            // check if banner is available
            $bannerResponse = \PrestaScan\Banner::getBanner();
            if (!empty($bannerResponse)) {
                $this->context->smarty->assign('banner', $bannerResponse);
            }
        }

        return $this->display(__FILE__, 'views/templates/admin/layouts/main.tpl');
    }

    public function updateModule()
    {
        \PrestaScan\Tools::fixMissingUpgrade();

        if (!Tools::getValue('upgrade_module')) {
            return false;
        }

        try {
            $update = new \PrestaScan\Update($this->context, $this);
            $update->processUpdateModule();
            Context::getContext()->cookie->__set('psscan_module_updated', true);
        } catch (\Exception $exp) {
            $error = $this->l('Error upgrading the module. Please refresh this page and try again.');
            Context::getContext()->cookie->__set('psscan_module_error', $error);
        }

        // Remove the 'upgrade_module' parameter from the query string
        $queryString = $_SERVER['QUERY_STRING'];
        $params = [];
        parse_str($queryString, $params);
        unset($params['upgrade_module']);
        $newQueryString = http_build_query($params);

        // Reload the page without the 'upgrade_module' parameter
        $url = $_SERVER['PHP_SELF'] . '?' . $newQueryString;
        header('Location: ' . $url);
        exit();
    }

    public function checkForErrorMessage()
    {
        $errorMessage = Context::getContext()->cookie->__get('psscan_module_error');
        if ($errorMessage) {
            Context::getContext()->cookie->__unset('psscan_module_error');
            return $errorMessage;
        }
        return false;
    }

    /**
    * Check if the user is logged in
    * 
    * @return bool
    * 
    */
    protected function isUserLoggedIn()
    {
        if ($this->isLoggedIn) {
            // Already logged in in current object context
            return true;
        }
        // Will throw an exception if token not a valid object
        try {
            $OAuth = new \PrestaScan\OAuth2\Oauth();
            $this->isLoggedIn = $OAuth->getAccessTokenObj() ? true : false;
        } catch (Exception $exp) {
            $this->isLoggedIn = false;
        }
        return $this->isLoggedIn;
    }

    protected function displayInitialScanAndScanProgress()
    {
        $displayInitialScan = true;
        $completedJobs = \PrestaScanQueue::getJobsByState(\PrestaScanQueue::$actionname['COMPLETED']);
        if (!empty($completedJobs)) {
            $displayInitialScan = false;
        }
        $progressScans = Configuration::get('PRESTASCAN_SCAN_PROGRESS');
        if (!empty($progressScans)) {
            $progressScans = json_decode($progressScans, true);
            foreach ($progressScans as $scan) {
                if ($scan) {
                    $displayInitialScan = false;
                    break;
                }
            }
        }

        $this->context->smarty->assign('displayInitialScan', $displayInitialScan);
        $this->context->smarty->assign('progressScans', $progressScans);
    }

    protected function assignAdminVariables($moduleNewVulnerabilitiesAlert)
    {
        $this->assignReportVariables();
        $this->assignSmartyStaticVariables();
        $this->assignSettingsPageUrl();
        $this->assignRegistrationVariables();

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
            'prestascansecurity_tpl_path' => _PS_MODULE_DIR_ . 'prestascansecurity/views/templates/admin/',
            'urlmodule' => $this->getPathUri(),
            'urlContact' => \PrestaScan\Tools::getCustomConfigValue('contact-us'),
        ]);
    }

    protected function assignSettingsPageUrl()
    {
        $settings_page_url = 'https://security.prestascan.com/user/profile';
        if (\Configuration::get('PRESTASCAN_TEST_MODE_OAUTH')) {
            $settings_page_url = 'http://127.0.0.1/user/profile';
        }
        $this->context->smarty->assign('settings_page_url', $settings_page_url);
    }

    protected function assignRegistrationVariables()
    {
        if ($this->isUserLoggedIn()) {
            // Already registered, nothing to do
            return true;
        }

        // If we are not logged in, we will display the data for the registration
        // Token used to communicate with the OAuth2 FrontController
        $moduleHash = Configuration::get('PRESTASCAN_SEC_HASH');
        $tokenFC = \PrestaScan\Tools::getHashByName('FCOauth', $moduleHash);
        $adminLink = $this->context->link->getAdminLink('AdminModules', false);
        if (strpos($adminLink, 'http') === false) {
            // Depending of the PS version, the getAdminLink behavior is not the same.
            // In some version, it will return the full url, but on other version only
            // the part after the shop URL.
            $adminLink = \PrestaScan\Tools::getShopUrl() . basename(_PS_ADMIN_DIR_) . '/' . $adminLink;
        }
        $urlConfigBo = $adminLink . '&configure=' .
                $this->name .'&tab_module=' .
                $this->tab . '&module_name=' .
                $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign([
            'prestascansecurity_tokenfc' => $tokenFC,
            'prestascansecurity_shopurl' => \PrestaScan\Tools::getShopUrl(),
            'prestascansecurity_e_firstname' => Context::getContext()->employee->firstname,
            'prestascansecurity_e_lastname' => Context::getContext()->employee->lastname,
            'prestascansecurity_e_email' => $this->context->employee->email,
            // For localhost development (see Oauth)
            'prestascansecurity_localoauth' => Tools::getValue('localoauth') ? 1 : 0,
            // For localhost development (see Oauth)
            'webcron_token' => Configuration::get('PRESTASCAN_WEBCRON_TOKEN'),
            'ps_shop_urls' => implode(';', array_map('urlencode', $this->getShopUrls())),
            // We retrive the module configuration URL in order to redirect into it after email verification
            // This URL will be kept localy in a cookie during registration
            'psscan_urlconfigbo' => urlencode(\PrestaScan\Tools::enforeHttpsIfAvailable($urlConfigBo)),
        ]);
    }

    protected function getShopUrls()
    {
        // Retrieve the list of shop urls
        // We will need to send those to the the server during the registration process
        $shopUrls = [];
        $http = Tools::usingSecureMode() ? 'https://' : 'http://';
        foreach (Shop::getShops(true) as $shopId) {
            $shop = new Shop($shopId['id_shop']);
            foreach ($shop->getUrls() as $u) {
                $shopUrls[] = \PrestaScan\Tools::enforeHttpsIfAvailable($http . $u['domain_ssl'] . $u['physical_uri'] . $u['virtual_uri']);
            }
        }
        return $shopUrls;
    }

    protected function includeAdminResources($moduleNewVulnerabilitiesAlert)
    {
        $vulnAlertHandler = new \PrestaScan\VulnerabilityAlertHandler($this);
        $mediaJsDef = array(
            'question_to_this_action' => $this->l('Removing or uninstalling modules in PrestaShop may pose risks if not done carefully, potentially causing system instability or data loss. Make sure to do this action first in a development environment. Contact your agency or our experts if required.'),
            'checkbox_risk_label' => $this->l('I understand the risks associated with removing or uninstalling modules in PrestaShop and agree to proceed with caution, prioritizing a development environment.'),
            'question_to_logout' => $this->l('Are you sure to log out?'),
            'js_error_occured' => $this->l('An error occured while generating the report. This may be due to a timeout. Please try again.'),
            'question_to_logout' => $this->l('Are you sure to log out?'),
            'js_description' => $this->l('Description'),
            'text_confirm_log_me_out' => $this->l('Yes, log me out'),
            'text_reload' => $this->l('Click here to refresh the page'),
            'text_yes' => $this->l('Yes'),
            'text_cancel' => $this->l('Cancel'),
            'text_yes_dismiss' => $this->l('dismiss'),
            'question_to_this_dismiss_action' => $this->l('You are about to remove this alert. You will need to redo a scan to get additional details. Are you sure to dismiss this alert?'),
            'banner_vulnerability_more_action' => $this->l('This alert is triggered because a new vulnerability was discovered in PrestaShop for this module. Your shop may be vulnerable if the module is not patched yet. Please contact your agency or our team of experts to fix the issue. Please redo a full scan of your module to get more details about the vulnerability.'),
            'banner_vulnerability_more_details' => $this->l('More details about this issue:'),
            'alert_new_modules_vulnerability' => !empty($moduleNewVulnerabilitiesAlert) ? true : false,
            'text_close' => $this->l('Close'),
            'text_refresh_status' => $this->l('Refresh status'),
            'text_refresh_module_status_required' => $this->l('It\'s requested to update the module in order to run a new scan.') . ' ' . $this->l('If you updated your module manually and still get this message, try refreshing the status of your module by clicking on the bouton "Refresh status" bellow.'),
            'text_error_not_logged_in' => $this->l('To launch a scan please log in or create an account. Having an account allows us to securely perform scans on your behalf and deliver accurate results.'),
            'text_login_btn' => $this->l('Log in or create an account'),
        );

        // Check cookie if update module is running
        $isModuleUpdated = Context::getContext()->cookie->__get('psscan_module_updated');
        if ($isModuleUpdated == true) {
            Context::getContext()->cookie->__unset('psscan_module_updated');
            $mediaJsDef['module_updated_confirmation_message'] = $this->l('Your module has been successfully updated.');
        }

        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            // We have some issues escaping strings with PS 1.6.X, so as from 1.7.0 only?
            Media::addJsDef($mediaJsDef);
        } else {
            $this->context->smarty->assign('mediaJsDef', $mediaJsDef);
        }

        $jsFiles = [
            'views/js/reports.js?v=' . $this->version,
            'views/js/authentication.js?v=' . $this->version,
            'views/js/modal.js?v=' . $this->version,
            'views/js/datatables.1.10.25.js',
            'views/js/dataTables.buttons.min.js',
            'views/js/file-size.js',
            'views/js/buttons.html5.min.js',
            'views/js/buttons.print.min.js',
            'views/js/jquery-ui.min.js',
        ];

        $cssFiles = [
            'views/css/datatables.1.10.25.css',
            'views/css/buttons.dataTables.min.css',
            'views/css/jquery-ui.min.css',
            'views/css/jquery-ui.structure.min.css',
            'views/css/jquery-ui.theme.min.css',
            'views/css/modal.css',
        ];

        foreach ($jsFiles as $jsFile) {
            $this->context->controller->addJS($this->_path . $jsFile, false);
        }

        foreach ($cssFiles as $cssFile) {
            $this->context->controller->addCSS($this->_path . $cssFile);
        }

        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            // Add custom CSS for PS 1.5
            $this->context->controller->addCSS($this->_path . 'views/css/admin_1.5.css');
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
        } else {
            $this->context->smarty->assign($prefix . '_results', false);
        }
    }

    /**
     * Retrieve the translated vulnerability name
     * This function has been place here instead of in an utility class un oder to be
     * able to use the translation system.
     */
    public function getVulnerabilityExtendedNameTranslated($shortName)
    {
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
     * This function is designed to bypass this limitation, by moving the namespaced call on the PHP side
     */
    public static function redirectTools($functionName, $param)
    {
        return PrestaScan\Tools::{$functionName}($param);
    }
}
