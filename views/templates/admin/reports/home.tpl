{*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
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
 *}

{assign var='scan_expired_text' value={l s='The last scan is too old to be considered, please rescan' mod='prestascansecurity'}}
{assign var='no_scan_done_yet_text' value={l s='No scan has yet been launched for the modules, you can do it now by clicking on the button below' mod='prestascansecurity'}}

{if isset($progressScans['directories_listing']) && !empty($progressScans['directories_listing'])
&& isset($progressScans['modules_vulnerabilities']) && !empty($progressScans['modules_vulnerabilities'])
&& isset($progressScans['core_vulnerabilities']) && !empty($progressScans['core_vulnerabilities'])
&& isset($progressScans['modules_unused']) && !empty($progressScans['modules_unused'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl"}
{elseif $displayInitialScan || !$prestascansecurity_isLoggedIn}
    {assign var='scan_text' value={l s='Launch a global scan to check the status of your shop' mod='prestascansecurity'}}
    {assign var='scan_text_btn' value={l s='Start a security scan' mod='prestascansecurity'}}
    {assign var='dataAction' value="generateGlobalReport"}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text aTextBtn=$scan_text_btn dataAction=$dataAction}
{else}

    <div class="all_result_data col-md-12">
        <h2 class="hook-title">{l s='Results from previous scans' mod='prestascansecurity'}</h2>

        {* Files and directories *}
        {include
            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/home/scan_summary.tpl"
            scan_title={l s='Files and Directories' mod='prestascansecurity'}
            scans=array($directories_listing_results)
            classcontainer='files'
            message_scan_outdated={l s='Your last vulnerability scan for your directories is outdated and should not be relied upon. Perform a new scan to ensure accurate results.' mod='prestascansecurity'}
            more_details_link='report-files-4'
            action_scan_btn='generateFilesReport'
        }

        {* Module scan *}
        {include
            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/home/scan_summary.tpl"
            scan_title={l s='Modules status' mod='prestascansecurity'}
            scans=array($modules_vulnerabilities_results, $modules_unused_results)
            classcontainer='modules'
            message_scan_outdated={l s='Your last vulnerability scan for your modules is outdated and should not be relied upon. Perform a new scan to ensure accurate results.' mod='prestascansecurity'}
            more_details_link='modules_vulnerabilities'
            action_scan_btn='generateModulesReport'
        }

        {* Core Vuln Scan *}
        {include
            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/home/scan_summary.tpl"
            scan_title={l s='Core Vulnerabilities' mod='prestascansecurity'}
            scans=array($core_vulnerabilities_results)
            classcontainer='core-vulnerabilities'
            message_scan_outdated={l s='Your last vulnerability scan for your directories is outdated and should not be relied upon. Perform a new scan to ensure accurate results.' mod='prestascansecurity'}
            more_details_link='tab-report-core-vulnerabilities'
            action_scan_btn='generateVulnerabilitiesReport'
        }
    </div>
{/if}

{if !$prestascansecurity_isLoggedIn}
    {assign var='infopanel_title' value={l s='Log in to start to scan' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='To launch a scan please log in or create an account. Having an account allows us to securely perform scans on your behalf and deliver accurate results. Click on the Login button on the top right corner to sign in or create a new account.' mod='prestascansecurity'}}

    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message}
{else}
    {assign var='infopanel_title' value={l s='Regularly Scan Your Site' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='Our experts discover new vulnerabilities daily. Keeping your scans up-to-date ensures that you will be promptly alerted if a new vulnerability has the potential to impact your shop.' mod='prestascansecurity'}}

    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message}
{/if}

