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

{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_panel.tpl"}

<div class="PrestascanSecurity">

    <div id="prestascansecurity_tabs" class="panel">
        {if !isset($smarty.get.activetab)}
            {assign var="prestascansecurity_activetab" value='report-modules'}
        {else}
            {assign var="prestascansecurity_activetab" value="{$smarty.get.activetab}"}
        {/if}
        <ul class="nav nav-tabs">
            <li id='report-modules' class="{if $prestascansecurity_activetab == 'report-modules'}active{/if}">
                <a href="#tab-report-modules" data-toggle="tab">{l s='Modules' mod='prestascansecurity'} {if isset($eosec_modules_install_state)}({$eosec_modules_install_state|count}){/if}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="tab-report-modules" class="tab-pane {if $prestascansecurity_activetab == 'report-modules'}active{/if}">
                {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}tab_modules_report.tpl"}
            </div>
        </div>
    </div>
</div>
