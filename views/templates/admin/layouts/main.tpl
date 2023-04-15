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

{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/javascript.tpl"}
{if !isset($smarty.get.activetab)}
	{assign var="eosecuritycheck_activetab" value='report-home'}
{else}
	{assign var="eosecuritycheck_activetab" value="{$smarty.get.activetab}"}
{/if}
{assign var="tabreportfiles" value=array("report-files","report-files-1","report-files-2","report-files-3","report-files-4")}
{assign var="tabreportmodules" value=array('report-modules','modules_vulnerabilities','modules_unused')}
<div id="prestascansecurity_main_container" data-urlreports="{$prestascansecurity_reports_ajax}" data-urlfileviewer="{$prestascansecurity_fileviewer_ajax}">

	<div id="flash-message"></div>

	{if isset($alert_new_modules_vulnerability) && !empty($alert_new_modules_vulnerability)}

		<div id="alert_vulnerabilities_banner" style="display: none;" data-description="{$alert_new_modules_vulnerability[0].description|escape:'htmlall':'UTF-8'}">
			<p>
				<span class="alert-title">
					{l s='MODULE VULNERABILITY ALERT' mod='prestascansecurity'}
					{if count($alert_new_modules_vulnerability) > 1}
						<span class="alert-number">
							(<strong>{$alert_new_modules_vulnerability|count}</strong> {l s='other alerts pending' mod='prestascansecurity'})
						</span>
					{/if}
				</span>

				<span class="alert-main">
					<span class="alert-main-title">
						{l s='Potential vulnerability detected in module ' mod='prestascansecurity'}
						<strong>{$alert_new_modules_vulnerability[0].name}.</strong>
					</span>
					&nbsp;<a href="javascript:void(0);" data-alertId="{$alert_new_modules_vulnerability[0].id}" class="dismiss-action">{l s='(Dismiss)' mod='prestascansecurity'}</a>
				</span>
				<span class="alert-vulnerability-action">{l s='More details' mod='prestascansecurity'}</span>
			</p>
		</div>
	{/if}

	{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/banner.tpl"}
	
	{if isset($module_upgrade_available) && $module_upgrade_available}
		{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/upgrade_module_banner.tpl"}
	{/if}
	<div class="panel">
		{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/menu.tpl"}
		<div class="prestascansecurity_container tab-content">
			<div id="tab-report-home" class="tab-pane {if $eosecuritycheck_activetab == 'report-home'}active{/if}">
			    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}reports/home.tpl"}
			</div>
			<div id="tab-report-files" class="tab-content tab-pane {if $eosecuritycheck_activetab|in_array:$tabreportfiles}active{/if}">
				<ul class="nav nav-tabs menu_container">
					<li class="hide report-files-1 menu-sous-element {if $eosecuritycheck_activetab == "report-files-1"}active{/if}">
						<a href="#report-files-1">{l s='Non-Standard files' mod='prestascansecurity'}</a>
					</li>
					<li class="hide report-files-2 menu-sous-element {if $eosecuritycheck_activetab == "report-files-2"}active{/if}">
						<a href="#report-files-2">{l s='Added or modified files' mod='prestascansecurity'}</a>
					</li>
					<li class="report-files-4 menu-sous-element {if ($eosecuritycheck_activetab == "report-files-4" || $eosecuritycheck_activetab == 'report-home' || $eosecuritycheck_activetab == "report-files") || (!$eosecuritycheck_activetab|in_array:$tabreportfiles && $eosecuritycheck_activetab != 'report-home' && $eosecuritycheck_activetab != "report-files")}active{/if}">
						<a href="#report-files-4">{l s='Directory protection' mod='prestascansecurity'}</a>
					</li>
					<li class="report-files-3 menu-sous-element {if $eosecuritycheck_activetab == "report-files-3"}active{/if}">
						<a href="#report-files-3">{l s='Infected files' mod='prestascansecurity'}</a>
					</li>
				</ul>
				{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}reports/files.tpl"}
			</div>
			<div id="tab-report-modules" class="tab-content tab-pane {if $eosecuritycheck_activetab|in_array:$tabreportmodules}active{/if}">
				<ul class="nav nav-tabs menu_container">
					<li class="modules_vulnerabilities menu-sous-element {if ($eosecuritycheck_activetab == 'modules_vulnerabilities' || $eosecuritycheck_activetab == 'report-home' || $eosecuritycheck_activetab == 'report-modules') || (!$eosecuritycheck_activetab|in_array:$tabreportmodules && $eosecuritycheck_activetab != 'report-home' && $eosecuritycheck_activetab != 'report-modules')}active{/if}">
						<a href="#modules_vulnerabilities">{l s='At-risk modules' mod='prestascansecurity'}</a>
					</li>
					<li class="modules_unused menu-sous-element {if $eosecuritycheck_activetab == 'modules_unused'}active{/if}">
						<a href="#modules_unused">{l s='Unused modules' mod='prestascansecurity'}</a>
					</li>
				</ul>
				{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}reports/modules.tpl"}
			</div>
			<div id="tab-report-core-vulnerabilities" class="tab-pane {if $eosecuritycheck_activetab == "report-core-vulnerabilities"}active{/if}">
				{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}reports/core_vulnerabilities.tpl"}
			</div>
		</div>
		{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/footer.tpl"}
	</div>

	<div id="popupDialog" title="">

	</div>
</div>
<div class="ui-widget-overlay" style="width: 100%; height: 100%;display:none;"></div>
