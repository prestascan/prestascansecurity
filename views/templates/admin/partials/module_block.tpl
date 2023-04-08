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

<li id="module-{$aModule.name}" class="module_block item well {$class}">
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
                <span class="vulnerabilities_count level_{$aModule.criticity}" title="{$aModule.vulnerabilities|count} {l s='vulnerabilitie(s) detected for this modules' mod='PrestascanSecurity'}">{$aModule.vulnerabilities|count}</span>
            {/if}
            {if $class && ($class == 'eosec_modules_maj_results') && isset($aModule.last_update_expire) && $aModule.last_update_expire}
                <span class="vulnerabilities_count level_high" title="{l s='The module\'s developer has not published an update for several years. This may pose a security risk if development is no longer active.'}">&nbsp;</span>
            {/if}
            <i class="arrow-icons arrow-down float-right">&#8964;</i>
        </div>
    </div>
    <div class="module_details">
        {if $class && ($class == 'eosec_modules_maj_results') && isset($aModule.last_update_expire) && $aModule.last_update_expire}
            <p class="msg-alert">{l s='The module\'s developer has not published an update for several years. This may pose a security risk if development is no longer active.'}</p>
        {/if}
        <p>
            {l s='Module status : ' mod='PrestascanSecurity'}<span class="title-module-details">{if $aModule.installed == 1}{l s='Installed' mod='PrestascanSecurity'}{else}{l s='Not installed' mod='PrestascanSecurity'}{/if} - {if $aModule.active == 1}{l s='Active' mod='PrestascanSecurity'}{else}{l s='Not active' mod='PrestascanSecurity'}{/if}</span>
        </p>
        {if $class && ($class == "modules_disabled_results" || $class == "modules_unistalled_results")}
            <div class="container_module_details_descr">
                <div class="module_detail_descr">
                    <p class="module_description">{if isset($aType) && $aType == 'moduleVulnerable'}{l s='Module description' mod='PrestascanSecurity'} : {/if}{$aModule.description}</p>
                </div>
                <div class="module_detail_actions">
                    <a href="javascript:void(0);" data-action="deleteModule" data-modulename="modulename">{l s='Delete'}</a>
                    {if $class == "modules_disabled_results"}
                        <a href="javascript:void(0);" data-action="uninstallModule" data-modulename="modulename">{l s='Uninstall'}</a>
                    {/if}
                </div>
            </div>
        {else}
            <p class="module_description">{l s='Module description' mod='PrestascanSecurity'} : {$aModule.description}</p>
        {/if}
        {if isset($aType) && $aType == 'moduleVulnerable'}
            {if isset($aModule.vulnerabilities)}
                <ul class="list-vulnerabilities-modules no-liste-style">
                    {foreach name=vulnerabilities from=$aModule.vulnerabilities item=aVulnerability}
                        <li class="vulnerability-status-{$aVulnerability.status}">
                            <p class="vulnerability-title"><span class="vulnerabilities_count level_{$aVulnerability.criticity} vulnerability_module" title="{if $aVulnerability.criticity == "high" || $aVulnerability.criticity == "critical"}{l s='High' mod='PrestascanSecurity'}{elseif $aVulnerability.criticity == "medium"}{l s='Medium' mod='PrestascanSecurity'}{else}{l s='Low' mod='PrestascanSecurity'}{/if}"></span><span>{$aVulnerability.type}</span></p>
                            <p><span class="vulnerability-version">{l s='From version : ' mod='PrestascanSecurity'}</span>
                                {if isset($aVulnerability.fromVersion) && $aVulnerability.fromVersion}
                                    {$aVulnerability.fromVersion}
                                {else}
                                    {l s='unknown' mod='PrestascanSecurity'}
                                {/if}
                                {l s='to version : ' mod='PrestascanSecurity'}
                                {if isset($aVulnerability.toVersion) && $aVulnerability.toVersion}
                                    {$aVulnerability.toVersion}
                                {else}
                                    {l s='unkown' mod='PrestascanSecurity'}
                                {/if}
                            </p>
                            {if (isset($aVulnerability.description[Context::getContext()->language->iso_code]))}
                                <p>{$aVulnerability.description[Context::getContext()->language->iso_code]}</p>
                            {elseif $aVulnerability.description !== null}
                                <p>{if isset($aVulnerability.description["en"])}{$aVulnerability.description["en"]}{else}{$aVulnerability.description}{/if}</p>
                            {else}
                                <p>{l s='No detail concerning this vulnerability' mod='PrestascanSecurity'}</p>
                            {/if}
                            <p>
                                {if isset($aVulnerability.cve_link) && $aVulnerability.cve_link}<a href="{$aVulnerability.cve_link}" target="_blank" class="btn-green-white">{l s='Link to CVE' mod='PrestascanSecurity'}</a>{/if}
                                {if $aVulnerability.public_link}<a href="{$aVulnerability.public_link}" target="_blank" class="btn-green-white">{l s='More details' mod='PrestascanSecurity'}</a>{/if}
                            </p>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        {/if}
    </div>
</li>