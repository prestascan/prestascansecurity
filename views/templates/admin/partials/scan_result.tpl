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

{if isset($aScanResult.scan_result_ttotal) && $aScanResult.scan_result_ttotal > 99}
    {assign var='total_result' value='99+'}
{else}
    {assign var='total_result' value=$aScanResult.scan_result_ttotal}
{/if}

{if isset($aScanResult.scan_result_criticity) && ($aScanResult.scan_result_criticity === 'high' || $aScanResult.scan_result_criticity === 'critical')}
    {assign var='scan_result_circle_color' value='red'}
{elseif isset($aScanResult.scan_result_criticity) && $aScanResult.scan_result_criticity === 'medium'}
    {assign var='scan_result_circle_color' value='yellow'}
{else}
    {assign var='scan_result_circle_color' value='green'}
{/if}
{assign var='is_scan_outdated' value=Prestascansecurity::redirectTools('isScanOutDated', $aScanResult.date)}

<h2 class="hook-title">{l s='Latest scan results' mod='prestascansecurity'}</h2>
{if $is_scan_outdated === true}
    <div class="scan_date_expired">{sprintf($message_scan_outdated, Prestascansecurity::redirectTools('formatDateString', $aScanResult.date))}</div>
{else}
    <span class="last_result_date">{Prestascansecurity::redirectTools('formatDateString', $aScanResult.date)}</span>
{/if}
<div class="last_result_data">
    <div class="result_modules_count">
        <div class="count_text col-lg-5 col-md-12">
            <div class="totalmodules circle_color_{$scan_result_circle_color}">{$total_result}</div>
        </div>
        <div class="count_desc col-lg-7 col-md-12">
            <h2>{$aScanResult.scan_result_ttotal} {$scan_result_item_type}</h2>
            <p>{$scan_result_text}</p>
        </div>
    </div>
    {if $scanType == 'modules_vulnerabilities'}
        {if isset($alert_new_modules_vulnerability) && !empty($alert_new_modules_vulnerability)}
            <div>
                <p class="text-center msg-alert f-bold">{l s='A new vulnerability has been recently discovered, check your alert above and re-do a scan if required to update your results' mod='prestascansecurity'}</p>
            </div>
        {/if}
        <div class="result_modules_details">
            <div class="col-md-6">
                <h2>{$module_result1_count}</h2>
                <p>{$module_result1_title}</p>
            </div>
            <div class="col-md-6">
                <h2>{$module_result2_count}</h2>
                <p>{$module_result2_title}</p>
            </div>
        </div>
    {/if}

    {if $scanType == 'core-vulnerabilities'}
        <div class="result_core-vulnerabilities_details">
            <div class="col-md-4">
                <h2>{$aScanResult.total_critical}</h2>
                <p>
                    <span style="color:#F45454">{l s='critical' mod='prestascansecurity'}</span>
                    <br/>
                    {l s='Vulnerabilities' mod='prestascansecurity'}
                </p>
            </div>
            <div class="col-md-4">
                <h2>{$aScanResult.total_high}</h2>
                <p>
                    <span style="color:#FFA801">{l s='high' mod='prestascansecurity'}</span>
                    <br/>
                    {l s='Vulnerabilities' mod='prestascansecurity'}
                </p>
            </div>
            <div class="col-md-4">
                <h2>{$aScanResult.total_medium + $aScanResult.total_low}</h2>
                <p>
                    <span style="color:#3AD29F">{l s='medium and low' mod='prestascansecurity'}</span>
                    <br/>
                    {l s='Vulnerabilities' mod='prestascansecurity'}
                </p>
            </div>
        </div>
    {/if}
</div>

<div class="row">
    <p class='button-center'><a class="btn-generate-report btn btn-default" data-action="{$dataAction}" href="javascript:void(0);" >{l s='Start a scan' mod='prestascansecurity'}</a></p>
</div>