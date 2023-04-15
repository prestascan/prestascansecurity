/*
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
 *
 * @author    Profileo Group - Complete list of authors and contributors to this software can be found in the AUTHORS file.
 * @copyright Since 2023 Profileo Group <contact@profileo.com> (https://www.profileo.com/fr/)
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
$(document).ready(function () {
    $(document).on('click', '#login-oauth2', openOauthPsScan);
});

function openOauthPsScan() {
    var winName = 'PrestaScan Security';
    var width = 870;
    var height = 750;

    // We want to center the popup over the parent window
    var topPos = window.top;
    var y = topPos.outerHeight / 2 + topPos.screenY - ( height / 2);
    var x = topPos.outerWidth / 2 + topPos.screenX - ( width / 2);

    // Our popup final parameters
    var params = "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width="+width+",height="+height+",left="+x+",top="+y;
    
    // We open our popup
    //var win = window.open(prestascansecurity_shopurl+'/module/prestascansecurity/oauth2?authorization=true&token='+prestascansecurity_tokenfc, "PrestaScan Authentication", params);
    var win = window.open(prestascansecurity_shopurl+'/?fc=module&module=prestascansecurity&controller=oauth2'
        +'&login=true'
        +'&token='+prestascansecurity_tokenfc
        +'&firstname='+prestascansecurity_e_firstname
        +'&lastname='+prestascansecurity_e_lastname
        +'&email='+prestascansecurity_e_email
        +'&webcrontoken='+webcron_token
        +'&ps_shop_urls='+ps_shop_urls
        +'&localoauth='+prestascansecurity_localoauth,
        winName, params);
    var timer = setInterval(function() { 
        if(win.closed) {
            clearInterval(timer);
            location.reload();
        }
    }, 500);
}
