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

{assign var='scan_expired_text' value={l s='The last scan is too old to be considered, please rescan' mod='prestascansecurity'}}
{assign var='no_scan_done_yet_text' value={l s='No scan has yet been launched for the modules, you can do it now by clicking on the button below' mod='prestascansecurity'}}

{if isset($progressScans['directories_listing']) && !empty($progressScans['directories_listing'])
&& isset($progressScans['modules_vulnerabilities']) && !empty($progressScans['modules_vulnerabilities'])
&& isset($progressScans['core_vulnerabilities']) && !empty($progressScans['core_vulnerabilities'])
&& isset($progressScans['modules_unused']) && !empty($progressScans['modules_unused'])}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/scan_in_progress.tpl"}
{elseif $displayInitialScan || !$prestascansecurity_isLoggedIn}
    {assign var='scan_text' value={l s='Launch a global scan to check the status of your shop' mod='prestascansecurity'}}
    {assign var='scan_text_btn' value={l s='Start a security scan' mod='prestascansecurity'}}
    {assign var='dataAction' value="generateGlobalReport"}
    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aText=$scan_text aTextBtn=$scan_text_btn dataAction=$dataAction}
{else}
    <div class="all_result_data col-md-12">
        <h2 class="hook-title">{l s='Results of last scan' mod='prestascansecurity'}</h2>
        <div data-link-parent="report-files" class="report-result files_results col-lg-4 col-md-12 {if (isset($directories_listing_status) && $directories_listing_status === 'outdated') || !isset($directories_listing_status)}scan_expired{/if}">

            {if isset($directories_listing_results) && isset($directories_listing_results.summary)}
                {if $directories_listing_results.summary.scan_result_criticity === 'high' || $directories_listing_results.summary.scan_result_criticity === 'critical'}
                    {assign var='scan_result_circle_color' value='red'}
                {elseif $directories_listing_results.summary.scan_result_criticity === 'medium'}
                    {assign var='scan_result_circle_color' value='yellow'}
                {else}
                    {assign var='scan_result_circle_color' value='green'}
                {/if}
            {else}
                {assign var='scan_result_circle_color' value='green'}
            {/if}

            <div class="result_data_container">
                <div class="result_data_total">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="totalmodules circle_color_{$scan_result_circle_color}"><span></span></div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <h2 class="scan_title">{l s='Files' mod='prestascansecurity'}</h2>
                        {if (isset($directories_listing_status) && $directories_listing_status === 'outdated')}
                            <div class="scan_date_expired">{$directories_listing_last_scan_outdated}</div>
                        {elseif isset($directories_listing_last_scan_date) && $directories_listing_last_scan_date !== false}
                            <div class="last_result_date">{l s='last scan on the' mod='prestascansecurity'} {$directories_listing_last_scan_date}</div>
                        {/if}
                    </div>
                </div>
                {if !isset($directories_listing_status)}
                    <div class="no_scan_done_yet">{$no_scan_done_yet_text}</div>
                {else}
                    {if isset($non_standards_files_results) && !empty($non_standards_files_results)}
                        <div class="result_data non_standards_files">
                            {assign var='scan_result_total' value=$non_standards_files_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='file(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='non standard' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$non_standards_files_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="report-files-1" class="report-result-child"}
                        </div>
                    {/if}
                    {if isset($added_or_modified_core_files_results) && !empty($added_or_modified_core_files_results)}
                        <div class="result_data added_or_modified_core_files">
                            {assign var='scan_result_total' value=$added_or_modified_core_files_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='file(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='added or modified' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$added_or_modified_core_files_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="report-files-2" class="report-result-child"}
                        </div>
                    {/if}
                    {if isset($infected_files_results) && !empty($infected_files_results)}
                        <div class="result_data infected_files">
                            {assign var='scan_result_total' value=$infected_files_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='file(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='infected' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$infected_files_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="report-files-3" class="report-result-child"}
                        </div>
                    {/if}
                    {if isset($directories_listing_results) && !empty($directories_listing_results)}
                        <div class="result_data directories_listing">
                            {assign var='scan_result_total' value=$directories_listing_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='document(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='unprotected' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$directories_listing_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="report-files-4" class="report-result-child"}
                        </div>
                    {/if}
                {/if}
            </div>
            <div class="scan_link row">
                {if !isset($directories_listing_status)}
                    <p><a class="btn-generate-report btn btn-default" data-action="generateFilesReport" href="javascript:void(0);" >{l s='Start a scan' mod='prestascansecurity'}</a></p>
                {else}
                    <a href="javascript:void(0);">{l s='View more details' mod='prestascansecurity'}</a>
                    <p><a class="btn-generate-report btn btn-default" data-action="generateFilesReport" href="javascript:void(0);" >{l s='Restart a scan' mod='prestascansecurity'}</a></p>
                {/if}
            </div>
        </div>

        <div data-link-parent="report-modules" class="report-result modules_results col-lg-4 col-md-12 {if (isset($modules_vulnerabilities_status) && $modules_vulnerabilities_status === 'outdated')|| !isset($modules_vulnerabilities_status)}scan_expired{/if}">

            {if isset($modules_vulnerabilities_results) && isset($modules_vulnerabilities_results.summary)}
                {if $modules_vulnerabilities_results.summary.scan_result_criticity === 'high' || $modules_vulnerabilities_results.summary.scan_result_criticity === 'critical'}
                    {assign var='scan_result_circle_color' value='red'}
                {elseif $modules_vulnerabilities_results.summary.scan_result_criticity === 'medium'}
                    {assign var='scan_result_circle_color' value='yellow'}
                {else}
                    {assign var='scan_result_circle_color' value='green'}
                {/if}
            {else}
                {assign var='scan_result_circle_color' value='green'}
            {/if}

            <div class="result_data_container">
                <div class="result_data_total">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="totalmodules circle_color_{$scan_result_circle_color}"><span></span></div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <h2 class="scan_title">{l s='Modules' mod='prestascansecurity'}</h2>
                        {if (isset($modules_vulnerabilities_status) && $modules_vulnerabilities_status === 'outdated')}
                            <div class="last_result_expired">{$scan_expired_text}</div>
                        {elseif isset($modules_vulnerabilities_last_scan_date) && $modules_vulnerabilities_last_scan_date !== false}
                            <div class="last_result_date">{l s='last scan on the' mod='prestascansecurity'} {$modules_vulnerabilities_last_scan_date}</div>
                        {/if}
                    </div>
                </div>
                {if !isset($modules_vulnerabilities_status)}
                    <div class="no_scan_done_yet">{$no_scan_done_yet_text}</div>
                {else}
                    {if isset($modules_vulnerabilities_results) && !empty($modules_vulnerabilities_results)}
                        <div class="result_data modules_vulnerabilities">
                            {assign var='scan_result_total' value=$modules_vulnerabilities_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='module(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='at risk' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$modules_vulnerabilities_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="modules_vulnerabilities" class="report-result-child"}
                        </div>
                    {/if}
                    {if isset($modules_unused_results) && !empty($modules_unused_results)}
                        <div class="result_data modules_unused">
                            {assign var='scan_result_total' value=$modules_unused_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='module(s) on %d are' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='inactive' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$modules_unused_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="modules_unused" class="report-result-child"}
                        </div>
                    {/if}
                {/if}
            </div>
            <div class="scan_link row">
                {if !isset($modules_vulnerabilities_status)}
                    <p><a class="btn-generate-report btn btn-default" data-action="generateModulesReport" href="javascript:void(0);" >{l s='Start a scan' mod='prestascansecurity'}</a></p>
                {else}
                    <a href="javascript:void(0);">{l s='View more details' mod='prestascansecurity'}</a>
                    <p><a class="btn-generate-report btn btn-default" data-action="generateModulesReport" href="javascript:void(0);" >{l s='Restart a scan' mod='prestascansecurity'}</a></p>
                {/if}
            </div>
        </div>

        <div data-link-parent="report-core-vulnerabilities" class="report-result vulnerabilities_results col-lg-4 col-md-12 {if (isset($core_vulnerabilities_status) && $core_vulnerabilities_status === 'outdated') || !isset($core_vulnerabilities_status)}scan_expired{/if}">

            {if isset($core_vulnerabilities_results) && isset($core_vulnerabilities_results.summary)}
                {if $core_vulnerabilities_results.summary.scan_result_criticity === 'high' || $core_vulnerabilities_results.summary.scan_result_criticity === 'critical'}
                    {assign var='scan_result_circle_color' value='red'}
                {elseif $core_vulnerabilities_results.summary.scan_result_criticity === 'medium'}
                    {assign var='scan_result_circle_color' value='yellow'}
                {else}
                    {assign var='scan_result_circle_color' value='green'}
                {/if}
            {else}
                {assign var='scan_result_circle_color' value='green'}
            {/if}

            <div class="result_data_container">
                <div class="result_data_total">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="totalmodules circle_color_{$scan_result_circle_color}"><span></span></div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <h2 class="scan_title">{l s='Vulnerabilities' mod='prestascansecurity'}</h2>
                        {if (isset($core_vulnerabilities_status) && $core_vulnerabilities_status === 'outdated')}
                            <div class="last_result_expired">{$scan_expired_text}</div>
                        {elseif isset($core_vulnerabilities_last_scan_date) && $core_vulnerabilities_last_scan_date !== false}
                            <div class="last_result_date">{l s='last scan on the' mod='prestascansecurity'} {$core_vulnerabilities_last_scan_date}</div> 
                        {/if}
                    </div>
                </div>
                {if !isset($core_vulnerabilities_status)}
                    <div class="no_scan_done_yet">{$no_scan_done_yet_text}</div>
                {else}
                    {if isset($core_vulnerabilities_results) && !empty($core_vulnerabilities_results)}
                        <div class="result_data">
                            {assign var='scan_result_total' value=$core_vulnerabilities_results.summary.scan_result_total}
                            {assign var='scan_result_text' value={l s='vulnerabilities(s) detected' sprintf=[$scan_result_total|escape:'html':'UTF-8'] mod='prestascansecurity'}}
                            {assign var='scan_result_text_type' value={l s='' mod='prestascansecurity'}}
                            {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}/partials/home_scan_result.tpl" scanType="non_standards_files" aScanResult=$core_vulnerabilities_results.summary scan_result_text_type=$scan_result_text_type scan_result_text=$scan_result_text id="report-core-vulnerabilities" class="report-result-child"}
                        </div>
                    {/if}
                {/if}
            </div>
            <div class="scan_link row">
                {if !isset($core_vulnerabilities_status)}
                    <p><a class="btn-generate-report btn btn-default" data-action="generateVulnerabilitiesReport" href="javascript:void(0);" >{l s='Start a scan' mod='prestascansecurity'}</a></p>
                {else}
                    <a href="javascript:void(0);">{l s='View more details' mod='prestascansecurity'}</a>
                    <p><a class="btn-generate-report btn btn-default" data-action="generateVulnerabilitiesReport" href="javascript:void(0);" >{l s='Restart a scan' mod='prestascansecurity'}</a></p>
                {/if}
            </div>
        </div>
    </div>
{/if}

{if !$prestascansecurity_isLoggedIn}
    {assign var='infopanel_title' value={l s='Log in to start to scan' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='To launch a scan please log in or create an account. Having an account allows us to securely perform scans on your behalf and deliver accurate results. Click \'Login\' on the top right corner to sign in or create a new account.' mod='prestascansecurity'}}

    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message}
{else}
    {assign var='infopanel_title' value={l s='Regularly Scan Your Site' mod='prestascansecurity'}}
    {assign var='infopanel_message' value={l s='Our experts discover new vulnerabilities daily. Keeping your scans up-to-date ensures that you will be promptly alerted if a new vulnerability has the potential to impact your shop.' mod='prestascansecurity'}}

    {include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}layouts/information_important.tpl" title=$infopanel_title message=$infopanel_message}
{/if}

