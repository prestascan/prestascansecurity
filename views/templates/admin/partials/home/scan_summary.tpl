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

{assign var='oldest_scan' value=Prestascansecurity::redirectTools('getOldestScan', $scans)}
{assign var='scan_highest_criticity' value=Prestascansecurity::redirectTools('getScanWithHighestCriticity', $scans)}
{assign var='at_least_one_scan_performed' value=Prestascansecurity::redirectTools('isContainingPerformedScan', $scans)}
{assign var='at_least_one_scan_outdated' value=Prestascansecurity::redirectTools('isContainingOutdatedScan', $scans)}
{assign var='scan_performed' value=$at_least_one_scan_performed}

<div data-link-parent="report-{$classcontainer}" class="report-result {$classcontainer}_results col-lg-4 col-md-12 {if $at_least_one_scan_outdated}scan_expired{/if}">

    <div class="result_data_container">

        {include
            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/home/scan_summary_top.tpl"
            scan_oldest=$oldest_scan
            scan_highest_criticity=$scan_highest_criticity
            scan_performed=$scan_performed
            scan_outdated=$at_least_one_scan_outdated
        }

        {if !at_least_one_scan_performed}
            <div class="no_scan_done_yet">{$no_scan_done_yet_text}</div>
        {else}

            {foreach from=$scans item=scan}
                {if $scan}
                    {assign var='scan_result_total' value=$scan.summary.scan_result_total}
                    {assign var='scan_type' value=$scan.summary.scan_type}

                    {if $scan_type === 'non_standards_files'}
                        {assign var='scan_result_text' value={l s='file(s)' mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='are not standard' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='report-files-1'}
                    {elseif $scan_type === 'added_or_modified_core_files'}
                        {assign var='scan_result_text' value={l s='file(s)' mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='are added or modified' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='report-files-2'}
                    {elseif $scan_type === 'infected_files'}
                        {assign var='scan_result_text' value={l s='file(s)' mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='are infected' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='report-files-3'}
                    {elseif $scan_type === 'directories_listing'}
                        {assign var='scan_result_text' value={l s='directories' mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='are not protected' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='report-files-4'}
                    {elseif $scan_type === 'modules_vulnerabilities'}
                        {assign var='scan_result_text' value={l s='out of %d modules are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='at-risk' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='modules_vulnerabilities'}
                    {elseif $scan_type === 'modules_unused'}
                        {assign var='scan_result_text' value={l s='out of %d modules are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='unused' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='modules_unused'}
                    {elseif $scan_type === 'core_vulnerabilities'}
                        {assign var='scan_result_text' value={l s='vulnerabilities detected' mod='prestascansecurity'}}
                        {assign var='scan_result_text_type' value={l s='' mod='prestascansecurity'}}
                        {assign var='scan_more_details_link' value='report-core-vulnerabilities'}
                    {/if}

                    <div class="result_data {$scan_type}">
                        {include
                            file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl"
                            scanType={$scan_type}
                            aScanResult=$scan.summary
                            scan_result_text_type=$scan_result_text_type
                            scan_result_text=$scan_result_text
                            id={$scan_type}
                            class="report-result-child"
                            scan_more_details_link=$scan_more_details_link
                        }
                    </div>
                {/if}
            {/foreach}

        {/if}
    </div>
    <div class="scan_link row">
        {if !$scan_performed}
            <p class='button-center'><a class="btn-generate-report btn btn-default" data-action="{$action_scan_btn}" href="javascript:void(0);" >{l s='Start a scan' mod='prestascansecurity'}</a></p>
        {else}
            <span class="report-result-child">
                <a href="#{$scan_more_details_link}">{l s='View more details' mod='prestascansecurity'}</a>
            </span>
            <p class='button-center'><a class="btn-generate-report btn btn-default" data-action="{$action_scan_btn}" href="javascript:void(0);" >{l s='Restart a scan' mod='prestascansecurity'}</a></p>
        {/if}
    </div>
</div>