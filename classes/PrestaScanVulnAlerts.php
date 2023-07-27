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
class PrestaScanVulnAlerts extends ObjectModel
{
    /** @var int ID */
    public $id;

    /** @var string module_name */
    public $module_name;

    /** @var string vulnerability_type */
    public $vulnerability_type;

    /** @var string vulnerability_data */
    public $vulnerability_data;

    /** @var string public_link */
    public $public_link;

    /** @var string cve */
    public $cve;

    /** @var string criticity */
    public $criticity;

    /** @var bool dismissed */
    public $dismissed;

    /** @var int state */
    public $employee_id_dismissed;

    /** @var datetime date_add */
    public $date_add;

    /** @var datetime date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prestascan_vuln_alerts',
        'primary' => 'id',
        'fields' => [
            'module_name' => ['type' => self::TYPE_STRING, 'required' => true],
            'vulnerability_type' => ['type' => self::TYPE_STRING, 'required' => true],
            'vulnerability_data' => ['type' => self::TYPE_STRING, 'required' => true],
            'public_link' => ['type' => self::TYPE_STRING, 'required' => false],
            'cve' => ['type' => self::TYPE_STRING, 'required' => false],
            'criticity' => ['type' => self::TYPE_STRING, 'required' => false],
            'dismissed' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
            'employee_id_dismissed' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'required' => false],
        ],
    ];

    public static function getAlertsNotDismissed()
    {
        $sql = 'SELECT *
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE `dismissed` = 0
            ORDER BY id DESC';
        return Db::getInstance()->executeS($sql);
    }

    public function saveVulnerabilityAlert($module_name, $vulnerability)
    {
        $this->module_name = pSQL($module_name);
        $this->vulnerability_type = pSQL($vulnerability['type']);
        $this->vulnerability_data = json_encode($vulnerability['description']);
        $this->public_link = pSQL($vulnerability['public_link']);
        $this->cve = pSQL($vulnerability['cve']);
        $this->criticity = pSQL($vulnerability['criticity']);
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = null;

        return $this->save();
    }

    public function dismissAlert($employeeId)
    {
        $this->dismissed = 1;
        $this->employee_id_dismissed = (int) $employeeId;
        return $this->save();
    }

    public static function dismissAll()
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`
                SET `dismissed` = 1, `date_upd` = NOW()
                WHERE `dismissed` = 0';
        return Db::getInstance()->execute($sql);
    }

    public static function truncate()
    {
        $sql = 'TRUNCATE TABLE `' . _DB_PREFIX_ . self::$definition['table'] . '`';
        return Db::getInstance()->execute($sql);
    }
}
