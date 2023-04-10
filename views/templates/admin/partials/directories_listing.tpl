{*
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
 *}

{assign var='scan_text' value={l s='Launch the Scan to check the status of your directories' mod='prestascansecurity'}}
{assign var='dataAction' value="generateDirectoriesProtection"}
{assign var='tooltiptext1' value={l s='Due to incorrect configurations or malware, your directories may become publicly accessible, allowing attackers to view, download, and potentially exploit sensitive data, compromise your system, or carry out additional malicious actions, consequently jeopardizing your privacy, security, and overall digital wellbeing.' mod='prestascansecurity'}}
{assign var='directorie_result_title' value={l s='Directorie(s) at risk' mod='prestascansecurity'}}

{if !empty($progressScans['directories_listing'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl"}
{elseif !empty($directories_listing_results)}
    {assign var='scan_result_item_type' value={l s='Directories(s)' mod='prestascansecurity'}}
    {assign var='scan_result_total' value=$directories_listing_results.summary.scan_result_fail_total}
    {assign var='scan_result_text' value={l s='on %d directories may be at risk on your PrestaShop' sprintf=[$directories_listing_results.summary.scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
    {assign var='file_action' value={l s='Contact your agency or contact our experts to verify this directory' mod='prestascansecurity'}}
    <div class="result_container col-md-4">
        {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_result.tpl"
            scanType="directories_listing"
            aScanResult=$directories_listing_results.summary
            scan_result_item_type=$scan_result_item_type
            scan_result_text=$scan_result_text
            class="scan_result"
            message_scan_outdated={l s='The last scan of %s is too old to be taken into consideration, please relaunch a new scan.' mod='prestascansecurity'}
        }
    </div>
    <div class="files_results eoresults col-md-8">
        <h2>{$directories_listing_results.summary.scan_result_fail_total} {$directorie_result_title}</h2>
        <div class="btntooltip">?<span class="tooltiptext">{$tooltiptext1}</span></div>
        {if $directories_listing_results.result|count > 0}
            {include
                file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/files_table.tpl"
                aFiles=$directories_listing_results.result
                aFileType=DirectoriesProtection
                class="directories_results"
            }
        {else}
            {assign var='no_result_text' value={l s='' mod='prestascansecurity'}}
            {include
                file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl"
                noResultText=$no_result_text
            }
        {/if}
    </div>
{else}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text dataAction=$dataAction}
{/if}