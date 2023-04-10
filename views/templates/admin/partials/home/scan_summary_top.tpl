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

{* inputs:
    scan_oldest=$oldest_scan
    scan_highest_criticity=$scan_highest_criticity
    scan_performed=$at_least_one_scan_performed
    scan_outdated=$at_least_one_scan_outdated
*}

{if $scan_performed}
    {assign var='scan_summary' value=$scan_highest_criticity.summary}

    {if $scan_summary.scan_result_criticity === 'high' || $scan_summary.scan_result_criticity === 'critical'}
        {assign var='scan_result_circle_color' value='red'}
    {elseif $scan_summary.scan_result_criticity === 'medium'}
        {assign var='scan_result_circle_color' value='yellow'}
    {else}
        {assign var='scan_result_circle_color' value='green'}
    {/if}

    {assign var='scan_date_formated' value=\PrestaScan\Tools::formatDateString($scan_oldest.summary.date)}
{/if}

{if $scan_outdated || !$scan_performed}
    {assign var='scan_result_circle_color' value='gray'}
{/if}

<div class="result_data_total">
    <div class="col-lg-6 col-md-6 col-xs-6">
        <div class="totalmodules circle_color_{$scan_result_circle_color}"><span></span></div>
    </div>

    <div class="col-lg-6 col-md-6 col-xs-6">
        <h2 class="scan_title">{$scan_title}</h2>

        {if $scan_performed && $scan_outdated}
            <div class="last_result_expired">{$message_scan_outdated}</div>
        {elseif $scan_performed}
            <div class="last_result_date">{l s='last scan on the' mod='prestascansecurity'} {$scan_date_formated}</div>
        {/if}

    </div>
</div>
