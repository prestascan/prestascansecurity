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
class PrestaScanQueue extends ObjectModel
{
    /** @var int ID */
    public $id;

    /** @var string jobid UUID */
    public $jobid;

    /** @var string action_name */
    public $action_name;

    /** @var string job_data */
    public $job_data;

    /** @var string state */
    public $state;

    /** @var string error_message */
    public $error_message;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prestascan_queue',
        'primary' => 'id',
        'fields' => [
            'jobid' => ['type' => self::TYPE_STRING, 'required' => true],
            'action_name' => ['type' => self::TYPE_STRING, 'required' => true],
            'job_data' => ['type' => self::TYPE_STRING, 'validate' => 'isUnsignedId', 'required' => false],
            'state' => ['type' => self::TYPE_STRING, 'validate' => 'isUnsignedId', 'required' => true],
            'error_message' => ['type' => self::TYPE_STRING, 'validate' => 'isUnsignedId', 'required' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'required' => false],
        ],
    ];

    public static $actionname = [
        'PROGRESS' => 'progress',
        'COMPLETED' => 'completed',
        'CANCEL' => 'cancel',
        // The scan has finished, we now need to retrive the data with oauth2
        'TORETRIEVE' => 'toretrieve',
        'ERROR' => 'error',
        'SUGGEST_CANCEL' => 'suggest_cancel',
    ];

    public static function isJobAlreadyInProgress($actionName)
    {
        $jobId = Db::getInstance()->getValue('
                SELECT `jobid`
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `action_name` = "'.pSQL($actionName).'" AND (
                    `state` = "' . pSQL(self::$actionname['PROGRESS']) . '" OR
                    `state` = "' . pSQL(self::$actionname['TORETRIEVE']) . '" OR
                    `state` = "' . pSQL(self::$actionname['SUGGEST_CANCEL']) . '")');
        return empty($jobId) ? false : $jobId;
    }

    public static function isJobAlreadyCompleted($actionName)
    {
        $jobId = Db::getInstance()->getValue('
                SELECT *
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `action_name` = "' . pSQL($actionName) . '" AND `state` = "' . pSQL(self::$actionname['COMPLETED']) . '"');

        return empty($jobId) ? false : $jobId;
    }

    public static function addJob($jobId, $actionName, $jobData = null)
    {
        $jobAddedSql = 'INSERT INTO `' . _DB_PREFIX_ . self::$definition['table'] . '`
            (`jobid`, `action_name`, `job_data`, `state`, `date_add`)
            VALUES ("'.pSQL($jobId).'", "'.pSQL($actionName).'", "'.pSQL($jobData).'", "' . pSQL(self::$actionname['PROGRESS']) . '" , NOW())';
        return Db::getInstance()->execute($jobAddedSql);
    }

    public static function getJobsByState($state)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `state` = "' . pSQL($state) . '"';
        $jobIds = Db::getInstance()->executeS($sql);

        return $jobIds;
    }

    public static function getJobsByJobid($jobid)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `jobid` = "' . pSQL($jobid) . '"';

        return Db::getInstance()->getRow($sql);
    }

    public static function updateJob($jobId, $state = 'progress', $message = '')
    {
        $jobAddedSql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`
            SET `state` = \'' . pSQL($state) . '\', `date_upd` = NOW()';
        if ($message != '') {
            $jobAddedSql .= ', `error_message` = \'' . pSQL($message) . '\'';
        }
        $jobAddedSql .= ' WHERE `jobid` = \'' . pSQL($jobId) . '\'';

        return Db::getInstance()->execute($jobAddedSql);
    }

    public static function deleteCompletedByActionName($actionname)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE action_name = "' . pSQL($actionname) . '"
            AND state = "' . pSQL(self::$actionname['COMPLETED']) . '"';

        return Db::getInstance()->execute($sql);
    }

    public static function getLastScanDate($action_name)
    {
        $sql = 'SELECT date_upd
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `action_name` = "' . pSQL($action_name) . '"'; 
        return Db::getInstance()->getValue($sql);
    }

    public static function truncate()
    {
        $sql = 'TRUNCATE TABLE `' . _DB_PREFIX_ . self::$definition['table'] . '`';
        return Db::getInstance()->execute($sql);
    }

    public static function checkJobsRunningForTooLong($time)
    {
        $jobId = Db::getInstance()->executeS('
            SELECT `jobid`, `action_name`
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE (`state` = "' . pSQL(self::$actionname['PROGRESS']) .'" OR 
                `state` = "' . pSQL(self::$actionname['TORETRIEVE']) . '") 
                AND date_add < DATE_SUB(now(), INTERVAL ' . (int)$time. ' MINUTE)');

        return empty($jobId) ? false : $jobId;
    }

    public static function getJobByActionNameAndState($actionName, $state)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
                WHERE `action_name` = "' . pSQL($actionName) . '" AND 
                `state` = "' .pSQL($state). '" ';
        return Db::getInstance()->getRow($sql);
    }
}
