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

{assign var='scan_text' value={l s='Launch the Scan to check the status of your unused modules' mod='prestascansecurity'}}
{assign var='dataAction' value="unusedModules"}
{assign var='tooltiptext1' value={l s='This list displays installed modules that are disabled.' mod='prestascansecurity'}}
{assign var='tooltiptext2' value={l s='This list displays uninstalled modules that remain on your file system. Modules will stay on your file system after uninstallation unless you explicitly specify their removal during the process.' mod='prestascansecurity'}}
{assign var='tooltipReport' value={l s='Download the list of modules and share it with your developer' mod='prestascansecurity'}}

{if !empty($modules_unused_results)}
    {assign var='module_result1_title' value={l s='Disabled module(s)' mod='prestascansecurity'}}
    {assign var='module_result1_count' value={$modules_unused_results.summary.total_disabled_modules}}
    {assign var='module_result2_title' value={l s='Uninstalled module(s)' mod='prestascansecurity'}}
    {assign var='module_result2_count' value={$modules_unused_results.summary.total_uninstalled_modules}}
    {assign var='scan_result_item_type' value={l s='Module(s)' mod='prestascansecurity'}}
    {assign var='scan_result_total' value=$modules_unused_results.summary.scan_result_total}
    {assign var='scan_result_text' value={l s='on %d are unused on your PrestaShop' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
{/if}

{if !empty($progressScans['modules_unused'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl" mustcancel=$progressScans['modules_unused'] datatype='modules_unused'}
{elseif !empty($modules_unused_results)}
    <div class="result_container col-md-4">        
        {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_result.tpl"
        scanType="modules_install_state"
        aScanResult=$modules_unused_results.summary
        scan_result_item_type=$scan_result_item_type
        scan_result_text=$scan_result_text
        module_result1_count=$module_result1_count
        module_result2_count=$module_result2_count
        module_result1_title=$module_result1_title
        module_result2_title=$module_result2_title
        class="scan_result" dataAction=$dataAction
        message_scan_outdated={l s='The last scan of %s is too old to be taken into consideration, please relaunch a new scan.' mod='prestascansecurity'}
    }
    </div>
    
    <div class="col-md-8">
        <div class="module_results eoresults col-md-6">         
            <h2>{$module_result1_count} {$module_result1_title}</h2>
            <div class="btntooltip">?<span class="tooltiptext">{$tooltiptext1}</span></div>
            {if $modules_unused_results.result.disabled|count != 0}
                {if $module_result1_count != 0}
                    <div class="btntooltip eoaction export-scan-results" data-type="modules_unused" data-subtype="disabled" data-action="exportScanResults">
                        <img src="/modules/prestascansecurity/views/img/export_report.png"/><span class="tooltiptext">{$tooltipReport}</span>
                    </div>
                {/if}
                <div class="scroll-overlay"></div>
                <ul id="modules" class="list-unstyled">
                    {if $modules_unused_results.result.disabled}
                        {foreach name=modules from=$modules_unused_results.result.disabled item=aModule}
                            {if !isset($aModule.is_dismissed) || $aModule.is_dismissed == 0}
                                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule aType="moduleUnused" class="modules_disabled_results"}
                            {/if}
                        {/foreach}
                        {foreach name=modules from=$modules_unused_results.result.disabled item=aModule}
                            {if isset($aModule.is_dismissed) && $aModule.is_dismissed}
                                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule aType="moduleUnused" class="modules_disabled_results"}
                            {/if}
                        {/foreach}
                    {/if}
                </ul>
            {else}
                {assign var='no_result_text' value={l s='The scanner did not detect any unused modules based on the latest scan. Be sure to perform regular scans to ensure that no new unused modules go unnoticed.' mod='prestascansecurity'}}
                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl" noResultText=$no_result_text}
            {/if}
        </div>
        <div class="module_results eoresults col-md-6">
            <h2>{$module_result2_count} {$module_result2_title}</h2>
            <div class="btntooltip">?<span class="tooltiptext">{$tooltiptext2}</span></div>
            {if $modules_unused_results.result.not_installed|count != 0} 
            {if $module_result2_count != 0}
                <div class="btntooltip eoaction export-scan-results" data-type="modules_unused" data-subtype="not_installed" data-action="exportScanResults">
                    <img src="/modules/prestascansecurity/views/img/export_report.png"/><span class="tooltiptext">{$tooltipReport}</span>
                </div>
            {/if}
                <div class="scroll-overlay"></div>
                <ul id="modules" class="list-unstyled">
                    {if $modules_unused_results.result.not_installed}
                        {foreach name=modules from=$modules_unused_results.result.not_installed item=aModule}
                            {if !isset($aModule.is_dismissed) || $aModule.is_dismissed == 0}
                                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule class="modules_uninstalled_results"}
                            {/if}
                        {/foreach}
                        {foreach name=modules from=$modules_unused_results.result.not_installed item=aModule}
                            {if isset($aModule.is_dismissed) && $aModule.is_dismissed}
                                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/module_block.tpl" aModule=$aModule class="modules_uninstalled_results"}
                            {/if}
                        {/foreach}
                    {/if}
                </ul>
            {else}
                {assign var='no_result_text' value={l s='The scanner did not detect any uninstalled modules based on the latest scan. Be sure to perform regular scans to ensure that no new uninstalled modules go unnoticed.' mod='prestascansecurity'}}
                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/no_results.tpl" noResultText=$no_result_text}
            {/if}
        </div>
    </div>


    {assign var='infopanel_title' value={l s='Remove uninstalled or disabled modules' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='Keeping disabled or uninstalled modules in your PrestaShop can pose security risks. Disabled and uninstalled modules may contain outdated code or unpatched vulnerabilities that can be exploited by attackers. Regularly removing unused modules reduces potential attack surfaces, ensuring a more secure and efficient online store experience. Maintain your store\'s integrity and safeguard customer data by keeping only the necessary, active modules.' mod='prestascansecurity'}}
    {assign var='infopanel_message2' value={l s='Removing modules in PrestaShop may pose risks if not done carefully, potentially causing system instability or data loss. Make sure to do this action first in a development environment. Contact your agency or our experts if required.' mod='prestascansecurity'}}

    {include
        file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl"
        title=$infopanel_title
        message=$infopanel_message
        message2=$infopanel_message2
    }

{else}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text dataAction=$dataAction}
{/if}