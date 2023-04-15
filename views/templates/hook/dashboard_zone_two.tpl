{*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
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

<section id="prestascansecurity_dashboard_hook" class="panel{if isset($alert_modules_vulnerability) && !empty($alert_modules_vulnerability)} prestascanalert{elseif isset($module_upgrade_available) && $module_upgrade_available} prestascanupdate{/if}">
    <div class="panel-heading mb-0">
        <img src="/modules/prestascansecurity/views/img/icon_dashboard.png"/> {l s='PrestaScan Security' mod='prestascansecurity'}
    </div>

    {if isset($alert_modules_vulnerability) && !empty($alert_modules_vulnerability)}
        {include file='./dashboard_security_alerts.tpl' alerts=$alert_modules_vulnerability}
    {elseif isset($module_upgrade_available) && $module_upgrade_available}
        {include file='./dashboard_update_module.tpl' module_link=$module_link}
    {/if}
</section>
