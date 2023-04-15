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

{assign var="tabreportfiles" value=["report-files","report-files-1","report-files-2","report-files-3","report-files-4"]}
{assign var="tabreportmodules" value=['report-modules','modules_vulnerabilities','modules_unused']}
<ul class="nav nav-tabs menu_container">
	<li id='report-home' class="menu_element {if $eosecuritycheck_activetab == 'report-home'}active{/if}">
        <a href="#tab-report-home" data-toggle="tab">{l s='Dashboard' mod='prestascansecurity'}</a>
    </li>
    <li id="report-files" class="menu_element {if $eosecuritycheck_activetab|in_array:$tabreportfiles}active{/if}">
        <a href="#tab-report-files" data-toggle="tab">{l s='Files scan' mod='prestascansecurity'}</a>
    </li>
    <li id='report-modules' class="menu_element {if $eosecuritycheck_activetab|in_array:$tabreportmodules}active{/if}">
        <a href="#tab-report-modules" data-toggle="tab">{l s='Modules scan' mod='prestascansecurity'}</a>
    </li>
    <li id="report-core-vulnerabilities" class="menu_element {if $eosecuritycheck_activetab == "report-core-vulnerabilities"}active{/if}">
        <a href="#tab-report-core-vulnerabilities" data-toggle="tab">{l s='PrestaShop vulnerabilities' mod='prestascansecurity'}</a>
    </li>
    <li id="contact" class="menu_element floatright">
        <a href="https://www.prestascan.com/fr/contactez-nous" target="_blank"><i class="icon-envelope"></i> <span>{l s='Contact' mod='prestascansecurity'}<span></a>
    </li>
    <li id="connexion" class="menu_element floatright">
        {if $prestascansecurity_isLoggedIn}
            <div class="dropdown">
              <a href="javascript:void(0);" id="login-oauth2" class='dropbtn'><i class="icon-user"></i> <span>{l s='Logged in' mod='prestascansecurity'}</span></a>
              <div class="dropdown-content">
                <div>
                    <a href="{$settings_page_url|escape:"html":'UTF-8'}" target="_blank">{l s='Settings' mod='prestascansecurity'}</a>
                </div>
                <div class='logout'>
                    <a href="javascript:void(0);">{l s='Logout' mod='prestascansecurity'}</a>
                </div>
              </div>
            </div>
        {else}
            <form id="pss-login-form" style="display:none;" method="post">
                <input
                    type="hidden"
                    name="login"
                    value="1"
                />
                <input
                    type="hidden"
                    name="token"
                    value="{$prestascansecurity_tokenfc|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="firstname"
                    value="{$prestascansecurity_e_firstname|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="lastname"
                    value="{$prestascansecurity_e_lastname|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="email"
                    value="{$prestascansecurity_e_email|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="webcrontoken"
                    value="{$webcron_token|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="ps_shop_urls"
                    value="{$ps_shop_urls|escape:'html':'UTF-8'}"
                />
                <input
                    type="hidden"
                    name="localoauth"
                    value="{$prestascansecurity_localoauth|escape:'html':'UTF-8'}"
                />
            </form>
            <a href="javascript:void(0);" id="login-oauth2"><i class="icon-user"></i> <span>{l s='Login' mod='prestascansecurity'}</span></a>
            {if isset($prestascansecurity_isLoggedIn_error)}
                {$prestascansecurity_isLoggedIn_error|escape:"html":'UTF-8'}
            {/if}
        {/if}
    </li>
</ul>