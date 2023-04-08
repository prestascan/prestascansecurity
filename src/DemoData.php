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

namespace PrestaScan;

class DemoData
{
    public static function unprotectedDirectories()
    {
        $filesDisplayReport = array();
        $filesDisplayReport['result'] = array();
        $filesDisplayReport['summary'] = array();

        $aDirectoryFail = array(
            'directory' => 'http://127.0.0.1/cache/',
            'status' => 'Fail',
            'action' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod'
        );
        $aDirectoryPass = array(
            'directory' => 'http://127.0.0.1/eosecurity_test/',
            'status' => 'Pass',
            'action' => ''
        );
        $filesDisplayReport['result'][] = $aDirectoryFail;
        $filesDisplayReport['result'][] = $aDirectoryFail;
        $filesDisplayReport['result'][] = $aDirectoryPass;
        $filesDisplayReport['result'][] = $aDirectoryPass;
        $filesDisplayReport['result'][] = $aDirectoryPass;
        $filesDisplayReport['result'][] = $aDirectoryPass;
        $filesDisplayReport['result'][] = $aDirectoryPass;

        $countreportFail = 0;
        foreach($filesDisplayReport['result'] as $report) {
            if ($report['status'] == 'Fail') {
                $countreportFail++;
            }
            
        }

        $filesDisplayReport['summary']['date'] = date('d F Y H:i');
        $filesDisplayReport['summary']['scan_type'] = 'document';   
        $filesDisplayReport['summary']['scan_result_total'] = count($filesDisplayReport['result']);
        $filesDisplayReport['summary']['scan_result_criticity'] = 'medium';
        $filesDisplayReport['summary']['scan_result_ttotal'] = $countreportFail;

        return $filesDisplayReport;
    }

    public static function vulnerableModulesData()
    {
        // to be deleted later
        $moduleDisplayReport = array();
        $moduleDisplayReport['vulnerable'] = array();
        $moduleDisplayReport['module_to_update'] = array();

        $aModule = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'vulnerabilities' => array(
                            array(
                                'status' => 'passed',
                                'from' => '4.0.0',
                                'to' => '4.1.9',
                                'type' => 'code_injection',
                                'description' => 'In PrestaShop XYZ before version 4.2.0, an attacker could inject malicious web code into the users web browsers by creating a malicious link. The problem was introduced in version 4.0.0 and is fixed in 4.2.0',
                                'public_link' => 'https://github.com/advisories/9',
                                'cve_link' => 'https://nvd.nist.gov/vuln/detail/',
                                'criticity' => 'high',
                            ),
                            array(
                                'status' => 'passed',
                                'from' => '4.0.0',
                                'to' => '4.1.9',
                                'type' => 'sql_injection',
                                'description' => 'Blind SQL injection with versions >= 4.0.0 and before 4.2.1. Fixed in 4.2.1. An attacker can use a Blind SQL injection to retrieve data or stop the MySQL service.',
                                'cve_link' => 'https://nvd.nist.gov/vuln/detail/',
                                'public_link' => '',
                                'criticity' => 'medium',
                            ),
                            array(
                                'status' => 'passed',
                                'from' => '',
                                'to' => '4.0.0',
                                'type' => 'sql_injection',
                                'description' => 'Blind SQL injection with versions >= 4.0.0 and before 4.2.1. Fixed in 4.2.1. An attacker can use a Blind SQL injection to retrieve data or stop the MySQL service.',
                                'cve_link' => '',
                                'public_link' => 'https://github.com/advisories/',
                                'criticity' => 'low',
                            )
                        ),
                        'active' => 1,
                        'installed' => 1,
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'criticity' => 'high',
                    );
        $moduleDisplayReport['vulnerable'][] = $aModule;
        $moduleDisplayReport['vulnerable'][0]["vulnerabilities"][0]["criticity"] = "low";
        $moduleDisplayReport['vulnerable'][0]["criticity"] = "medium";
        $moduleDisplayReport['vulnerable'][] = $aModule;
        $moduleDisplayReport['vulnerable'][1]["vulnerabilities"][0]["criticity"] = "low";
        $moduleDisplayReport['vulnerable'][1]["criticity"] = "low";
        unset($moduleDisplayReport['vulnerable'][1]["vulnerabilities"][1]);
        unset($moduleDisplayReport['vulnerable'][1]["vulnerabilities"][2]);
        $moduleDisplayReport['vulnerable'][] = $aModule;
        $moduleDisplayReport['vulnerable'][] = $aModule;

        $aModule = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'vulnerabilities' => array(
                            array(
                                'status' => 'passed',
                                'from' => '4.0.0',
                                'to' => '4.1.9',
                                'type' => 'code_injection',
                                'description' => 'In PrestaShop XYZ before version 4.2.0, an attacker could inject malicious web code into the users web browsers by creating a malicious link. The problem was introduced in version 4.0.0 and is fixed in 4.2.0',
                                'public_link' => 'https://github.com/advisories/9',
                                'cve_link' => 'https://nvd.nist.gov/vuln/detail/',
                            ),
                            array(
                                'status' => 'passed',
                                'from' => '4.0.0',
                                'to' => '4.2.0',
                                'type' => 'sql_injection',
                                'description' => 'Blind SQL injection with versions >= 4.0.0 and before 4.2.1. Fixed in 4.2.1. An attacker can use a Blind SQL injection to retrieve data or stop the MySQL service.',
                                'public_link' => 'https://github.com/advisories/9',
                                'cve_link' => 'https://nvd.nist.gov/vuln/detail/',
                            )
                        ),
                        'active' => 1,
                        'installed' => 1,
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'last_update' => '2023-02-02',
                        'last_update_expire' => false,
                    );
        $moduleDisplayReport['module_to_update'][] = $aModule;
        $moduleDisplayReport['module_to_update'][] = $aModule;
        $moduleDisplayReport['module_to_update'][0]["last_update_expire"] = true;

        $moduleDisplayReport['summary']['date'] = date('d F Y H\hi'); 
        $moduleDisplayReport['summary']['scan_type'] = 'module';
        $moduleDisplayReport['summary']['scan_result_total'] = 456;
        $moduleDisplayReport['summary']['scan_result_criticity'] = 'medium';
        $moduleDisplayReport['summary']['scan_result_ttotal'] = count($moduleDisplayReport['vulnerable']) + count($moduleDisplayReport['module_to_update']);
        $moduleDisplayReport['summary']['total_vulnerable'] = count($moduleDisplayReport['vulnerable']);
        $moduleDisplayReport['summary']['total_module_to_update'] = count($moduleDisplayReport['module_to_update']);

        return $moduleDisplayReport;
    }

    public static function unusedModulesData()
    {
        // to be deleted later
        $moduleDisplayReport = array();
        $moduleDisplayReport['disabled'] = array();
        $moduleDisplayReport['disabled'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => true,
                    );
        $moduleDisplayReport['disabled'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => true,
                    );
        $moduleDisplayReport['disabled'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => true,
                    );
        $moduleDisplayReport['disabled'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => true,
                    );

        $moduleDisplayReport['not_installed'] = array();
        $moduleDisplayReport['not_installed'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => false,
                    );
        $moduleDisplayReport['not_installed'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => false,
                    );
        $moduleDisplayReport['not_installed'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => false,
                    );
        $moduleDisplayReport['not_installed'][] = array(
                        'name' => 'statsbestsuppliers',
                        'version' => '5.0.1',
                        'displayName' => 'Test test',
                        'description' => 'Permettez aux utilisateurs de vous contacter depuis la page de paiement.',
                        'author' => 'PrestaShop',
                        'active' => false,
                        'installed' => false,
                    );

        $moduleDisplayReport['summary']['date'] = date('d F Y H\hi'); 
        $moduleDisplayReport['summary']['scan_type'] = 'module';
        $moduleDisplayReport['summary']['scan_result_total'] = 150;
        $moduleDisplayReport['summary']['scan_result_criticity'] = 'low';
        $moduleDisplayReport['summary']['scan_result_ttotal'] = count($moduleDisplayReport['disabled']) + count($moduleDisplayReport['not_installed']);
        $moduleDisplayReport['summary']['total_disabled_modules'] = count($moduleDisplayReport['disabled']);
        $moduleDisplayReport['summary']['total_uninstalled_modules'] = count($moduleDisplayReport['not_installed']);
        
        return $moduleDisplayReport;
    }

    public static function coreVulnerabilitiesDisplayReport() 
    {
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2019-13461'),
            'description' => array('en' => array('value' => 'In PrestaShop before 1.7.6.0 RC2, the id_address_delivery and id_address_invoice parameters are affected by an Insecure Direct Object Reference vulnerability due to a guessable value sent to the web application during checkout. An attacker could leak personal customer information. This is PrestaShop bug #14444.')),
            'severity' => array('value' => 'High'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.6.0.1'),
            'to' => array('value' => '1.7.6.5'),
            'difficulty' => array('value' => 'Critical'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2020-15082'),
            'description' => array('en' => array('value' => 'In PrestaShop from version 1.6.0.1 and before version 1.7.6.6, the dashboard allows rewriting all configuration variables. The problem is fixed in 1.7.6.6')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'No'),
            'bo' => array('value' => 'Yes'),
            'from' => array('value' => '1.5.0.0'),
            'to' => array('value' => '1.7.6.5'),
            'difficulty' => array('value' => 'Medium'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/30b6a7bdaca9cb940d3ce462906dbb062499fc30')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2020-15160'),
            'description' => array('en' => array('value' => 'PrestaShop from version 1.7.5.0 and before version 1.7.6.8 is vulnerable to a blind SQL Injection attack in the Catalog Product edition page with location parameter. The problem is fixed in 1.7.6.8')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.7.5.0'),
            'to' => array('value' => '1.7.6.7'),
            'difficulty' => array('value' => 'High'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2018-20717'),
            'description' => array('en' => array('value' => 'In the orders section of PrestaShop before 1.7.2.5, an attack is possible after gaining access to a target store with a user role with the rights of at least a Salesman or higher privileges. The attacker can then inject arbitrary PHP objects into the process and abuse an object chain in order to gain Remote Code Execution. This occurs because protection against serialized objects looks for a 0: followed by an integer, but does not consider 0:+ followed by an integer.')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.6.0.1'),
            'to' => array('value' => '1.7.2.4'),
            'difficulty' => array('value' => 'Medium'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2019-13461'),
            'description' => array('en' => array('value' => 'In PrestaShop before 1.7.6.0 RC2, the id_address_delivery and id_address_invoice parameters are affected by an Insecure Direct Object Reference vulnerability due to a guessable value sent to the web application during checkout. An attacker could leak personal customer information. This is PrestaShop bug #14444.')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.6.0.1'),
            'to' => array('value' => '1.7.6.5'),
            'difficulty' => array('value' => 'Critical'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2019-13461'),
            'description' => array('en' => array('value' => 'In PrestaShop before 1.7.6.0 RC2, the id_address_delivery and id_address_invoice parameters are affected by an Insecure Direct Object Reference vulnerability due to a guessable value sent to the web application during checkout. An attacker could leak personal customer information. This is PrestaShop bug #14444.')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.6.0.1'),
            'to' => array('value' => '1.7.6.5'),
            'difficulty' => array('value' => 'High'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );
        $coreVulnerabilitiesDisplayReport['result'][] = array(
            'cve' => array('value' => '2019-13461'),
            'description' => array('en' => array('value' => 'In PrestaShop before 1.7.6.0 RC2, the id_address_delivery and id_address_invoice parameters are affected by an Insecure Direct Object Reference vulnerability due to a guessable value sent to the web application during checkout. An attacker could leak personal customer information. This is PrestaShop bug #14444.')),
            'severity' => array('value' => 'Critical'),
            'fo' => array('value' => 'Yes'),
            'bo' => array('value' => 'No'),
            'from' => array('value' => '1.6.0.1'),
            'to' => array('value' => '1.7.6.5'),
            'difficulty' => array('value' => 'Medium'),
            'tech_notes' => array('value' => 'https://github.com/PrestaShop/PrestaShop/commit/d1726e8a0af488a6bdf5bb2eb11eebe54aa3d105')
        );

        $countReportCritical = $countReportHigh = $countReportMedium  = 0;
        if (!empty($coreVulnerabilitiesDisplayReport['result'])) {
            foreach($coreVulnerabilitiesDisplayReport['result'] as $report) {
                if ($report['severity']['value'] == 'Critical') {
                    $countReportCritical++;
                } elseif ($report['severity'] ['value']== 'High') {
                    $countReportHigh++;
                } else {
                    $countReportMedium++;
                } 
            }
        } 

        $coreVulnerabilitiesDisplayReport['summary']['date'] = date('d F Y H\hi'); 
        $coreVulnerabilitiesDisplayReport['summary']['scan_type'] = 'vulnerability';        
        $coreVulnerabilitiesDisplayReport['summary']['prestashop_version'] = '1.7.8.7';
        $coreVulnerabilitiesDisplayReport['summary']['scan_result_criticity'] = 'high';
        $coreVulnerabilitiesDisplayReport['summary']['scan_result_ttotal'] = $countReportCritical + $countReportHigh + $countReportMedium;  
        $coreVulnerabilitiesDisplayReport['summary']['scan_result_total'] = count($coreVulnerabilitiesDisplayReport['results']);
        $coreVulnerabilitiesDisplayReport['summary']['total_critical'] = $countReportCritical;
        $coreVulnerabilitiesDisplayReport['summary']['total_high'] = $countReportHigh;
        $coreVulnerabilitiesDisplayReport['summary']['total_medium'] = $countReportMedium;
        return $coreVulnerabilitiesDisplayReport;
    }
}
