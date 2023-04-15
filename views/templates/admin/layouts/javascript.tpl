{*
 * Copyright 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * 
 * For questions or comments about this software, contact Maxime Morel-Bailly <security@prestascan.com>
 * 
 * @author Profileo Group - Complete list of authors and contributors to this software can be found in the AUTHORS file.
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

<script type="text/javascript">
    {if isset($mediaJsDef)}
        {foreach from=$mediaJsDef key=key item=value} 
            var {$key} = {$value|json_encode:escape:"html"};
        {/foreach}
    {/if}
    {if isset($prestascansecurity_isLoggedIn)}
        var prestascansecurity_isLoggedIn = {$prestascansecurity_isLoggedIn|var_export:true};
    {/if}
	var prestascansecurity_tokenfc = {$prestascansecurity_tokenfc|json_encode:escape:"html"};
	var prestascansecurity_shopurl = {$prestascansecurity_shopurl|json_encode:escape:"html"};
	var prestascansecurity_e_firstname = {$prestascansecurity_e_firstname|json_encode:escape:"html"};
	var prestascansecurity_e_lastname = {$prestascansecurity_e_lastname|json_encode:escape:"html"};
	var prestascansecurity_e_email = {$prestascansecurity_e_email|json_encode:escape:"html"};
	var webcron_token = {$webcron_token|json_encode:escape:"html"};
	var ps_shop_urls = {$ps_shop_urls|json_encode:escape:"html"};
	var prestascansecurity_localoauth = {$prestascansecurity_localoauth|json_encode:escape:"html"};
</script>
