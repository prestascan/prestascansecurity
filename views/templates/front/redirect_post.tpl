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
Redirecting...
<form id="register_form" action="{$registration_url}" method="post" style="display:none;">
    <input type="text" id="createclient" name="createclient" value="{$createclient|escape:'html':'UTF-8'}">
    <input type="text" id="firstname" name="firstname" value="{$firstname|escape:'html':'UTF-8'}">
    <input type="text" id="lastname" name="lastname" value="{$lastname|escape:'html':'UTF-8'}">
    <input type="text" id="email" name="email" value="{$email|escape:'html':'UTF-8'}">
    <input type="text" id="redirect" name="redirect" value="{$redirect|escape:'html':'UTF-8'}">
    <input type="text" id="webcrontoken" name="webcrontoken" value="{$webcrontoken|escape:'html':'UTF-8'}">
    <input type="text" id="ps_shop_urls" name="ps_shop_urls" value="{$ps_shop_urls|escape:'html':'UTF-8'}">
</form>

{literal}
<script type="text/javascript">
    document.forms["register_form"].submit();
</script>
{/literal}
