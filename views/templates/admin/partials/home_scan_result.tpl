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

<div class="{$class} result_data_desc point_color_{$scan_result_circle_color} col-lg-12 col-md-12">
    <a href="#{$scan_more_details_link}">{$aScanResult.scan_result_ttotal} {$scan_result_text} <span class="text_color_{$scan_result_circle_color}">{$scan_result_text_type}</span></a>
</div>