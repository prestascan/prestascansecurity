{*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
 * 
 * @author Profileo Group - Complete list of authors and contributors to this software can be found in the AUTHORS file.
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

{assign var='scan_text' value={l s='Launch the Scan to check the status of your modules at-risk' mod='prestascansecurity'}}
{assign var='dataAction' value="generateModuleReport"}
{assign var='tooltiptext1' value={l s='This list displays modules flagged for known vulnerabilities. There may be false positives if there is uncertainty regarding the starting version of the vulnerability.' mod='prestascansecurity'}}
{assign var='tooltiptext2' value={l s='This list displays modules that are not up to date or have not been updated by their authors for years.' mod='prestascansecurity'}}
{if !empty($modules_vulnerabilities_results)}
    {assign var='module_result1_title' value={l s='vulnerable module(s)' mod='prestascansecurity'}}
    {assign var='module_result1_count' value={$modules_vulnerabilities_results.vulnerable|count}}
    {assign var='module_result2_title' value={l s='module(s) to update' mod='prestascansecurity'}}
    {assign var='module_result2_count' value={$modules_vulnerabilities_results.module_to_update|count}}
    {assign var='scan_result_item_type' value={l s='Module(s)' mod='prestascansecurity'}}
    {assign var='scan_result_total' value=$modules_vulnerabilities_results.summary.scan_result_total}
    {assign var='scan_result_text' value={l s='on %d may be at risk on your PrestaShop' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
{/if}

{if !empty($progressScans['modules_vulnerabilities'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl"}
{elseif !empty($modules_vulnerabilities_results)}
    <div class="result_container col-md-4">
        {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/scan_result.tpl"
            scanType='modules_vulnerabilities'
            aScanResult=$modules_vulnerabilities_results.summary
            scan_result_item_type=$scan_result_item_type
            scan_result_text=$scan_result_text
            module_result1_count=$module_result1_count
            module_result2_count=$module_result2_count
            module_result1_title=$module_result1_title
            module_result2_title=$module_result2_title
            class="scan_result"
            message_scan_outdated={l s='The last scan of %s is too old to be taken into consideration, please relaunch a new scan.' mod='prestascansecurity'}
        }
    </div>

    <div class="col-md-8">
        <div class="module_results eoresults col-md-6">
            <h2>{$module_result1_count} {$module_result1_title}</h2>
            <div class="btntooltip">?<span class="tooltiptext">{$tooltiptext1}</span></div>
            {if $module_result1_count != 0}
                <div class="scroll-overlay"></div>
                <ul id="modules" class="list-unstyled">
                    {if $modules_vulnerabilities_results.vulnerable}
                        {foreach name=modules from=$modules_vulnerabilities_results.vulnerable item=aModule}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule aType="moduleVulnerable" class="modules_vulnerabilities_results"}
                        {/foreach}
                    {/if}
                </ul>
            {else}
                {assign var='no_result_text' value={l s='The scanner did not detect any vulnerable modules based on the latest scan. Be sure to perform regular scans to ensure that no new vulnerable modules are detected.' mod='prestascansecurity'}}
                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl" noResultText=$no_result_text}
            {/if}
        </div>
        <div class="module_results eoresults col-md-6">
            <h2>{$module_result2_count} {$module_result2_title}</h2>
            <div class="btntooltip">?<span class="tooltiptext">{$tooltiptext2}</span></div>
            {if $module_result2_count != 0} 
                <div class="scroll-overlay"></div>
                <ul id="modules" class="list-unstyled">
                    {if $modules_vulnerabilities_results.module_to_update}
                        {foreach name=modules from=$modules_vulnerabilities_results.module_to_update item=aModule}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule class="eosec_modules_maj_results"}
                        {/foreach}
                    {/if}
                </ul>
            {else}
                {assign var='no_result_text' value={l s='The scanner did not detect modules to update based on the latest scan. Be sure to perform regular scans to check for updates and stay current.' mod='prestascansecurity'}}
                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl" noResultText=$no_result_text}
            {/if}
        </div>
        {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/partners_logos.tpl"}
    </div>

    {assign var='infopanel_title' value={l s='Maintain your modules updated' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='It\'s essential to keep your modules up-to-date, even if they are not flagged as vulnerable. Outdated or unmaintained modules are more susceptible to vulnerabilities and exploitation. The list of modules to update displayed above is based solely on modules purchased and maintained at addons.prestashop.com. For third-party modules, you will need to manually check for updates to ensure their security.' mod='prestascansecurity'}}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message}

{else}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text dataAction=$dataAction}
{/if}