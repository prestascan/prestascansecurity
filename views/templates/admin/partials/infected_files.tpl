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

{assign var='scan_text' value={l s='The scanning of infected and vulnerable files is currently being done manually by our team of experts. However, we are actively working to integrate this feature into this module. In the meantime, you can contact your agency to check and clean your website or seek assistance from our expert team.' mod='prestascansecurity'}}
{assign var='scan_text_btn' value={l s='Contact us' mod='prestascansecurity'}}
{assign var='dataLink' value='https://www.prestascan.com/fr/contactez-nous'}
{assign var='dataAction' value="generateInfectedFilesReport"}

{include file="{$prestascansecurity_tpl_path|escape:'htmlall':'UTF-8'}partials/start_scan_overlay.tpl" aTextNormal=true aTextBtn=$scan_text_btn aText=$scan_text dataAction=$dataAction dataLink=$dataLink}
