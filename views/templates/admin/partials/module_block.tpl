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

<li id="module-{$aModule.name}" class="module_block item well {$class} {if isset($aModule.is_dismissed) && $aModule.is_dismissed}dismissed{/if}">
    <div class="module_name">
        <div class="module-logo-thumb-list">
          <img src="/modules/{$aModule.name}/logo.png" alt="{$aModule.displayName}" width="45">
        </div>
        <div class="module-details">
            <span class='module-details-label'>
                <strong>{$aModule.displayName}</strong>
            </span>
            </br><small>V{$aModule.version} - <b>{$aModule.author}</b></small>
            {if (isset($aModule.criticity))}
                <span class="vulnerabilities_count level_{$aModule.criticity}" title="{$aModule.vulnerabilities|count} {l s='vulnerabilitie(s) detected for this modules' mod='prestascansecurity'}">{$aModule.vulnerabilities|count}</span>
            {/if}
            {if $class && ($class == 'eosec_modules_maj_results') && isset($aModule.last_update_expire) && $aModule.last_update_expire}
                <span class="vulnerabilities_count level_high" title="{l s='The module\'s developer has not published an update for several years. This may pose a security risk if development is no longer active.' mod='prestascansecurity'}">&nbsp;</span>
            {/if}
            <i class="arrow-icons arrow-down float-right">&#8964;</i>
        </div>
    </div>
    <div class="module_details">
        {if $class && ($class == 'eosec_modules_maj_results') && isset($aModule.last_update_expire) && $aModule.last_update_expire}
            <p class="msg-alert">{l s='The module\'s developer has not published an update for several years. This may pose a security risk if development is no longer active.' mod='prestascansecurity'}</p>
        {/if}
        {if isset($aType) && $aType == 'moduleVulnerable' && isset($aModule.module_link) && !empty($aModule.module_link[0])}
            <p class="module_link">{l s='Module page : ' mod='prestascansecurity'}<a href="{$aModule.module_link[0]}" target="_blank"><b>{$aModule.module_link[0]}</b></a></p>
        {/if}
        <p>
            {l s='Module status : ' mod='prestascansecurity'}
            <span class="title-module-details">
                {if $aModule.installed == 1}
                    {l s='Installed' mod='prestascansecurity'}
                {else}
                    {l s='Not installed' mod='prestascansecurity'}
                {/if} - 
                {if $aModule.active == 1}
                    {l s='Active' mod='prestascansecurity'}
                {else}
                    {l s='Not active' mod='prestascansecurity'}
                {/if}
            </span>
        </p>
        {if $class && ($class == "modules_disabled_results" || $class == "modules_uninstalled_results")}
            <div class="container_module_details_descr">
                <div class="module_detail_descr">
                    <p class="module_description">{if isset($aType) && $aType == 'moduleVulnerable'}{l s='Module description' mod='prestascansecurity'} : {/if}{$aModule.description}</p>
                </div>
                <div class="module_detail_actions">
                    <a href="javascript:void(0);" data-action="deleteModule" data-modulename="{$aModule.name}">{l s='Delete' mod='prestascansecurity'}</a>
                    {if $class == "modules_disabled_results"}
                        <a href="javascript:void(0);" data-action="uninstallModule" data-modulename="{$aModule.name}">{l s='Uninstall' mod='prestascansecurity'}</a>
                    {/if}
                </div>
            </div>
        {else}
            <p class="module_description">{l s='Module description' mod='prestascansecurity'} : {$aModule.description}</p>
        {/if}
        {if isset($aType) && $aType == 'moduleVulnerableToUpdate' && isset($aModule.module_link) && !empty
        ($aModule.module_link)}
            {if is_array($aModule.module_link) && isset($aModule.module_link[0])}
                {* In some instance, the API return an array *}
                {assign var="module_link" value="{$aModule.module_link[0]}"}
            {else}
                {assign var="module_link" value="{$aModule.module_link}"}
            {/if}
            <p>
            <a href="{$module_link}" class="btn-green-white" target="_blank">{l s='Link of the module on addons' mod='prestascansecurity'}</a>
            </p>
        {/if}
        {if isset($aType) && $aType == 'moduleVulnerable'}
            {if isset($aModule.vulnerabilities)}
                <ul class="list-vulnerabilities-modules no-liste-style">
                {assign var="countVulnerable" value=0}
                    {foreach name=vulnerabilities from=$aModule.vulnerabilities item=aVulnerability}
                        {assign var="countVulnerable" value=$aModule.vulnerabilities|count}
                        <li class="vulnerability-status-{$aVulnerability.status}">
                            <p class="vulnerability-title"><span class="vulnerabilities_count level_{$aVulnerability.criticity} vulnerability_module" title="{if $aVulnerability.criticity == "high" || $aVulnerability.criticity == "critical"}{l s='High' mod='prestascansecurity'}{elseif $aVulnerability.criticity == "medium"}{l s='Medium' mod='prestascansecurity'}{else}{l s='Low' mod='prestascansecurity'}{/if}"></span><span>{$aVulnerability.type}</span></p>
                            <p><span class="vulnerability-version">{l s='From version: ' mod='prestascansecurity'}</span>
                                {if isset($aVulnerability.fromVersion) && $aVulnerability.fromVersion}
                                    {$aVulnerability.fromVersion}
                                {else}
                                    {l s='unknown' mod='prestascansecurity'}
                                {/if}
                                {l s='to version: ' mod='prestascansecurity'}
                                {if isset($aVulnerability.toVersion) && $aVulnerability.toVersion}
                                    {$aVulnerability.toVersion}
                                {else}
                                    {l s='unkown' mod='prestascansecurity'}
                                {/if}
                            </p>
                            <p><span class="vulnerability-description">{l s='Description: ' mod='prestascansecurity'}</span>
                                {if (isset($aVulnerability.description[Context::getContext()->language->iso_code]))}
                                    {$aVulnerability.description[Context::getContext()->language->iso_code]}
                                {elseif $aVulnerability.description !== null}
                                    {if isset($aVulnerability.description["en"])}{$aVulnerability.description["en"]}{else}{$aVulnerability.description}{/if}
                                {else}
                                    <p>{l s='No detail concerning this vulnerability' mod='prestascansecurity'}
                                {/if}
                            </p>
                            <p>
                                {if isset($aType) && $aType == 'moduleVulnerable' && (isset($aVulnerability.author_discovery) && $aVulnerability.author_discovery!= "")}
                                    <p><span class="vulnerability-author-discovery">{l s='Discovery Author(s): ' mod='prestascansecurity'}</span><span>{$aVulnerability.author_discovery}</span></p>
                                {/if}
                            </p>
                            <p>
                                {if isset($aVulnerability.cve) && $aVulnerability.cve}<a href="{$aVulnerability.cve}" target="_blank" class="btn-green-white">{l s='Link to CVE' mod='prestascansecurity'}</a>{/if}
                                
                                {if !empty($aVulnerability.public_link)}
                                    {assign var="public_link_arr" value=[$aVulnerability.public_link]}
                                    {if strpos($aVulnerability.public_link, ',') !== false}
                                        {$public_link_arr = explode(',', $aVulnerability.public_link)}
                                    {/if}
                                    {foreach $public_link_arr as $key=>$public_link}
                                        <a href="{$public_link}" target="_blank" class="btn-green-white">
                                            {if $key == 0}
                                                {l s='More detail' mod='prestascansecurity'}
                                            {else}
                                                {l s='Additional detail' mod='prestascansecurity'}
                                            {/if}
                                        </a>
                                    {/foreach}
                                {/if}
                            </p>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        {/if}
        {if isset($alert_description)}
            <p class='msg-alert'>{$alert_description|escape:'htmlall':'UTF-8'}</p>
        {/if}
        {if (isset($aType) && $aType == 'moduleVulnerable') || (isset($class) && $class == 'modules_to_update')}
            <a href="javascript:void(0);" class="btn-green-white eoaction dismiss-vulnerability" data-subtype="{$class|replace:'_results':''}" data-type="modules_vulnerabilities" data-value="{$aModule.name}" data-action="{if isset($aModule.is_dismissed) && $aModule.is_dismissed}reopen{else}dismissed{/if}" data-vulnerabilitiesCount="{if isset($countVulnerable)}{$countVulnerable}{/if}"{if isset($aModule.count_vulerability)}data-countVulnerabilities="{$aModule.count_vulerability}"{/if}>{if isset($aModule.is_dismissed) && $aModule.is_dismissed}{l s='Reopen' mod='prestascansecurity'}{else}<span>X&nbsp;</span>{l s='Dismiss' mod='prestascansecurity'}{/if}</a>
        {else}
            <a href="javascript:void(0);" class="btn-green-white eoaction dismiss-vulnerability" data-subtype="{$class|replace:'_results':''}" data-type="modules_unused" data-value="{$aModule.name}" data-action="{if isset($aModule.is_dismissed) && $aModule.is_dismissed}reopen{else}dismissed{/if}">{if isset($aModule.is_dismissed) && $aModule.is_dismissed}{l s='Reopen' mod='prestascansecurity'}{else}<span>X&nbsp;</span>{l s='Dismiss' mod='prestascansecurity'}{/if}</a>
        {/if}
    </div>
</li>
