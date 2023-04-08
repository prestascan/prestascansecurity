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

<div class="scan_in_progress">
    <div class="scanview">{l s='Scan in progress' mod='prestascansecurity'}</div>
    <span class="logo_security"><img src="/modules/prestascansecurity/views/img/logo.png"/></span>
    <span class="scanmodules"><img src="/modules/prestascansecurity/views/img/scan-module.gif"/ width="225"></span>
    <p class="scan_title">{l s='Scan in progress' mod='prestascansecurity'}</p>
    <p>{l s='It might take a few moments to complete this operation.' mod='prestascansecurity'}<br/>{l s='This page can be closed at any time, this will not cancel the scan.' mod='prestascansecurity'}<br/>{*{l s='An alert will be sent to you by email once the scan is complete.' mod='prestascansecurity'}*}</p>
</div>