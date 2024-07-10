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
        <a href="{$urlContact}" target="_blank"><i class="icon-envelope"></i> <span>{l s='Contact' mod='prestascansecurity'}<span></a>
    </li>
    <li id="connexion" class="menu_element floatright">
        {if $prestascansecurity_isLoggedIn}
            <div class="dropdown">
              <a href="javascript:void(0);" id="login-oauth2" class='dropbtn'><i class="icon-user"></i> <span>{l s='Logged in' mod='prestascansecurity'}</span></a>
              <div class="dropdown-content">
                {if isset($email_user) && $email_user != ''}
                    <div>
                        <a href='javascript:void(0);' title='{$email_user|escape:"html":'UTF-8'}' class='profile_email_user'>{$email_user|escape:"html":'UTF-8'}</a>
                    </div>
                {/if}
                <div>
                    <a href="{$settings_page_url|escape:"html":'UTF-8'}" target="_blank">{l s='Settings' mod='prestascansecurity'}</a>
                </div>
                <div class='logout'>
                    <a href="javascript:void(0);">{l s='Logout' mod='prestascansecurity'}</a>
                </div>
              </div>
            </div>
        {else}
            <a href="javascript:void(0);" id="login-oauth2"><i class="icon-user"></i> <span>{l s='Login' mod='prestascansecurity'}</span></a>
        {/if}
    </li>
    <li id="refresh_subscription" class="menu_element floatright">
        {if $prestascansecurity_isLoggedIn && isset($subscription) && !$subscription}
            <div class="refresh-subscription">
                <a href="javascript:void(0);" id="link_refresh_subscription" class='dropbtn' title="{l s='Click here to update your subscription status (after purchase).' mod='prestascansecurity'}">
                    <i class="icon-refresh"></i>
                </a>
            </div>
        {/if}
    </li>
    <li id="subscription" class="menu_element floatright full-height">
    {if $prestascansecurity_isLoggedIn}
        <div class="avantage dropdown">
            <a href="javascript:void(0);" id="subscription-status" class='dropbtn'>
                {if isset($subscription) && $subscription}
                    <i class="icon-check"></i>
                    <span>{l s='Active subscription' mod='prestascansecurity'}</span>
                {else}
                    <i class="icon-euro"></i>
                    <span>{l s='Premium benefits' mod='prestascansecurity'}</span>
                {/if}
            </a>
            <div id="btn-action-avantage" class="dropdown-content">
                {if isset($subscription) && $subscription}
                    <div class="show-advantage">
                        <a href="https://www.profileo.com/index.php?module=eo_sub&fc=module&controller=list" target="_blank">{l s='Subscription management and details' mod='prestascansecurity'}</a>
                    </div>
                    <div class="show-advantage">
                        <a href="{$settings_page_url|escape:"html":'UTF-8'}" target="_blank">{l s='Manage my technical agency contact' mod='prestascansecurity'}</a>
                    </div>
                {else}
                    <div class="show-advantage">
                        <a href="https://www.profileo.com/prestascan-security-buy" target="_blank">{l s='See the benefits' mod='prestascansecurity'}</a>
                    </div>
                {/if}
            </div>
            <div class="clearer"></div>
        </div>
    {/if}
    </li>
</ul>
