{*
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
 *}

{assign var='scan_text' value={l s='Launch the Scan to list known PrestaShop vulnerabilities' mod='prestascansecurity'}}
{assign var='dataAction' value="generateCoreVulnerabilities"}

{if !empty($progressScans['core_vulnerabilities'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl"  mustcancel=$progressScans['core_vulnerabilities'] datatype='core_vulnerabilities'}
{elseif empty($core_vulnerabilities_results)}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text dataAction=$dataAction}
{else}

    {assign var='prestashop_version' value=$core_vulnerabilities_results.summary.prestashop_version}

    {assign var='scan_result_total' value=$core_vulnerabilities_results.summary.scan_result_ttotal}

    {if $scan_result_total == 1}
        {assign var='scan_result_item_type' value={l s='core vulnerability' mod='prestascansecurity'}}
    {else}
        {assign var='scan_result_item_type' value={l s='core vulnerabilities' mod='prestascansecurity'}}
    {/if}

    {assign var='scan_result_text' value={l s='on PrestaShop ' mod='prestascansecurity'}|cat:$prestashop_version}

    <div class="result_container col-md-4">
        {include
            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_result.tpl"
            scanType="core-vulnerabilities"
            aScanResult=$core_vulnerabilities_results.summary
            scan_result_item_type=$scan_result_item_type
            scan_result_text=$scan_result_text
            class="scan_result"
            message_scan_outdated={l s='The last scan of %s is too old to be taken into consideration, please relaunch a new scan.' mod='prestascansecurity'}
        }
    </div>
    <div class="files_results eoresults col-md-8">
        {if $core_vulnerabilities_results.result|count > 0}
            {include
                file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/files_table.tpl"
                aFiles=$core_vulnerabilities_results.result
                aFileType=corevulnerabilities id="coreVulnerabilities"
                class="files_results"
            }
        {else}
            {assign var='no_result_text' value={l s='The scanner did not detect any vulnerabilities in your PrestaShop version based on the latest scan. Be sure to perform regular scans to ensure that no new vulnerabilities are detected.' mod='prestascansecurity'}}
            {include
                file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl"
                noResultText=$no_result_text
            }
        {/if}
    </div>

    {assign var='infopanel_title' value={l s='Maintain an Updated PrestaShop' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='New PrestaShop versions often include security fixes. Ensuring your version is up-to-date is crucial. If you don\'t plan to update your PrestaShop immediatly, be certain to patch any high, or critical vulnerabilities.' mod='prestascansecurity'}}
    {assign var='infopanel_message_2' value={l s='Our experts or your agency can provide guidance and assistance in addressing vulnerabilities for you.' mod='prestascansecurity'}}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message message2=$infopanel_message_2}

{/if}
