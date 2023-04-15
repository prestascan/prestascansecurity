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

{assign var="banner_text" value={l s='Update your PrestaScan module as soon as possible to benefit from the latest features' mod='prestascansecurity'}}
<div class="informationPanel row">
    <div class="banner_logo col-md-3 col-sm-3">
        <img src="/modules/prestascansecurity/views/img/Logo_PSSecurity.svg"/>
    </div>
    <div class="banner_text col-md-6 col-sm-6">
        {$banner_text|replace:'PrestaScan':'Presta<span>Scan</span>'}
    </div>
    <div class="banner_btn col-md-3 col-sm-3">
        <a id="updateModuleBtn" class="btn btn-default" data-action="updateModule" href="javascript:void(0);" >{l s='Update module' mod='prestascansecurity'}</a>
    </div>
</div>
