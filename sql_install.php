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

// This file is an array of SQL tables that need to be installed/uninstalled.
// You may add new tables to install, but you should also use upgrade scripts to install the new table
// during module upgrade.

$sql = array();

// Profileo Notice : We would prefer using MySQL UUID solution for the primary key with a BINARY storage
// (with then BIN_TO_UUID / UUID_TO_BIN functions for the requests.
// However, this is only available in MySql 8.0 or later. So we prefer to use a char(36) to store
// our UUID. The volume of data stored will be reduced on client side, so this will not create
// performance bottleneck
$sql[_DB_PREFIX_.'prestascan_queue'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestascan_queue` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `jobid` char(36) NOT NULL,
              `action_name` varchar(255) NOT NULL,
              `job_data` TEXT NULL,
              `state` ENUM (\'progress\',\'cancel\',\'completed\',\'error\',\'toretrieve\') NOT NULL,
              `error_message` VARCHAR(255) NULL,
              `date_add` datetime NOT NULL,
              `date_upd` datetime NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_jobid` (`jobid`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_.'vulnerability_alerts'] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestascan_vuln_alerts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module_name` varchar(255) NOT NULL,
            `vulnerability_type` varchar(255) NOT NULL,
            `vulnerability_data` TEXT NULL,
            `public_link` TEXT NULL,
            `cve` TEXT NULL,
            `criticity` varchar(20) NULL,
            `dismissed` BOOLEAN NOT NULL DEFAULT 0,
            `employee_id_dismissed` INT(11) NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NULL,
            PRIMARY KEY (`id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
