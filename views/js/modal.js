/*
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
 */

$(function () {

  var prestascanSecurity_Modal = window.prestascanSecurity_Modal = {
    config : {
      jQuerySelectors : {
        prestascanModal : $("#prestascanModal"),
        modalBody : $("#prestascanModal .modal_body"),
        modalContent : $("#prestascanModal .modal_content"),
        closeModal : $("#prestascanModal .closeModal"),
      },
      cssSelector : {
        modalBody : "#prestascanModal .modal_body",
      },
    },
    init : function() {
      this.initHandlers();
    },
    initHandlers : function() {
      // close popup
      this.config.jQuerySelectors.closeModal.on(
        'click',
        $(this),
        this.closeDialog
      );

      // Click to disable or delete a module
      this.config.jQuerySelectors.prestascanModal.on(
        'click',
        this.dialogClickHandler
      );

      // When the user clicks anywhere outside of the modal, close it
      window.onclick = function(event) {
        if (event.target == prestascanModal) {
          prestascanSecurity_Modal.closeDialog();
        }
      };
    },
    closeDialog : function()
    {
      prestascanSecurity_Modal.config.jQuerySelectors.modalBody.html('');
      prestascanSecurity_Modal.config.jQuerySelectors.prestascanModal.hide();
    },
    createDialog : function(mainContent, buttons, customHtml) {
      var modalBody = $(window.prestascanSecurity_Modal.config.cssSelector.modalBody);
      modalBody.html("<p>" + mainContent + "</p>");
      // Add additional html to popup
      if (typeof customHtml !== 'undefined') {
        modalBody.append(customHtml);
      }
      // Add button to popup
      if (buttons.length > 0) {
        prestascanSecurity_Modal.createDialogButtons(modalBody, buttons);
      }
      $('#prestascanModal').show();
      // Center the popup
      prestascanSecurity_Modal.positionDialog();
    },
    createDialogButtons : function(modalBody, buttons)
    {
      var numbtn = buttons.length;
      var btnHTML = modalbtnclass = '';
      if (numbtn == 1) {
        var modalbtnclass = 'single_button';    
      } else if (numbtn == 2) {
        var modalbtnclass = 'two_buttons';
      }
      var btnHTML = '<div class="modal-buttonset ' + modalbtnclass + '">';
      $.each(buttons, function(i,val){
        var btnclass = val.class;
        var btntext = val.text;
        var clickhandler = val.click;
        btnHTML += '<button type="button" ';
        // Ajouter les data- au bouton
        if (params = val.additionalparams) {
          $.each(params, function(key,value){
            btnHTML += 'data-' + key + '="' + value + '" ';
          });
        }
        btnHTML += 'data-clickhandler="' + clickhandler + '" class="modal-button ' + btnclass + '">' + btntext + '</button>';
      });
      btnHTML += '</div>';

      modalBody.append(btnHTML);
    },
    dialogClickHandler : function(e) {
      var $target = $(e.target);
      // Checkbox
      if ($target.hasClass('chkConfirmModuleUninstall') || $target.parent().hasClass('chkConfirmModuleUninstall'))
      {
        // It's a click on the checkbox to confirm deletion of or unsinstall of modules
        // Retrieve the good element (the checkbox)
        $element = $target.hasClass('chkConfirmModuleUninstall') ? $target.parent() : $target;
        if ($element .is(':checked')) {
          $('.modal-buttonset button.danger').removeClass('disabled').addClass('validated');
        } else {
          $('.modal-buttonset button.danger').addClass('disabled').removeClass('validated');
        }
      } else if ($target.hasClass('modal-button'))
      {
        var clickhandler = $target.data('clickhandler').split('.');
        window[clickhandler[0]][clickhandler[1]](e);
      }
    },
    positionDialog : function() 
    {
      var modalHeight = this.config.jQuerySelectors.modalContent.outerHeight();
      var modalHalfHeight = modalHeight / 2;
      this.config.jQuerySelectors.modalContent.css('top', 'calc(50% - ' + modalHalfHeight + 'px)');
    }
  }

  prestascanSecurity_Modal.init();
});
