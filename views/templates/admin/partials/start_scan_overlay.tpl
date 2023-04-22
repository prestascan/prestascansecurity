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

{assign var="scan_btn_text" value={l s='Start a scan' mod='prestascansecurity'}}
{if isset($aTextBtn)}
    {assign var="scan_btn_text" value=$aTextBtn}
{/if}
<div id="scan_overlay" class="no_scan_yet">
    <span class="logo_security"><img src="{$urlmodule}views/img/logo.png"/></span>
    <br/>
    <h2 class='scan_text {if isset($aTextNormal)}normal{/if}'>{$aText}</h2>
    <br/><br/>
    <div class="row">
        <p class='button-center'><a class="{if !isset($dataLink)}btn-generate-report {/if}btn btn-default" data-action="{$dataAction}" href="{if !isset($dataLink)}javascript:void(0);{else}{$dataLink}{/if}" {if isset($dataLink)}target='_blank'{/if}>{$scan_btn_text}</a></p>
    </div>
</div>