<?php
/**
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/).
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

/**
 * Update main function for module.
 *
 * @param Prestascansecurity $module
 *
 * @return bool
 */
function upgrade_module_1_1_6($module)
{
    \Configuration::updateGlobalValue('PRESTASCAN_SUBS_STATE', 0);
    \Configuration::updateGlobalValue('PRESTASCAN_SUBS_LAST_CHECK', null);

    $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "prestascan_vuln_alerts` CHANGE `module_name` `module_name` VARCHAR(255) NULL;";
    $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "prestascan_vuln_alerts` ADD `is_core` BOOLEAN NOT NULL DEFAULT FALSE AFTER `employee_id_dismissed`; ";
    $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "prestascan_queue` ADD `last_date_retrieve` datetime NULL;";

    foreach ($sql as $query) {
        Db::getInstance()->execute($query);
    }

    // Delete all dismiss cache file
    $reports = new \PrestaScan\Reports\Report();
    foreach($reports->getDismissedCacheFiles() as $files) {
        \PrestaScan\Tools::deleteReport($files);
    }

    return true;
}
