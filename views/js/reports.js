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

  var prestascanSecurity = window.prestascanSecurity = {
    config : {
      jQuerySelectors : {
        // main container
        container : $("#prestascansecurity_main_container"),
        // CTA update module
        updateModuleBtn : $("#updateModuleBtn"),
        // Action to delete or disable a module
        deleteOrDisableModuleBtn : $(".module_detail_actions a"),
        // Core Vulnerabilities table
        coreVulnerabilitiesTable : $('#coreVulnerabilities tbody'),
        // Dismiss alert banner
        dismissAlertBanner : $("#alert_vulnerabilities_banner a.dismiss-action"),
        // More action alert banner module vulnerable
        moreActionAlertModule : $("#alert_vulnerabilities_banner .alert-vulnerability-action"),
        // Cancel job action
        cancelJobAction : $("#prestascansecurity_main_container .scan_in_progress.suggest_cancel a.cancel-job-action"),
        // Home container
        homeContainer : $('#tab-report-home'),
        // Connexion and config buttons
        connexionCfgBtn : $('#connexion'),
        // Refresh subscription button
        refreshSubscription : $('#refresh_subscription'),
      },
      cssSelector : {
        buttonReportGenerate : ".btn-generate-report",
        alertModuleVulnerabilitiesBanner : "#alert_vulnerabilities_banner",
        btnDetailSummaryDashboard : ".report-result-child a",
        btnMainMenuElement : ".menu_element",
        btnSubMenuElement : ".menu-sous-element a",
        btnLogOut : ".logout a",
        arrowModuleExpand : ".module-details i.arrow-icons",
        dismissVulnerability : "a.eoaction.dismiss-vulnerability",
        exportScanResults : ".eoaction.export-scan-results",
        btnRefreshSubscription : ".refresh-subscription a",
      },
      dataTables : {
        coreVulnerabilitiesDT : false
      },
      checkScanJobsProgresion : false
    },
    init : function() {
      //display message in popup if exists
      this.showModuleUpdatedConfirmationPopup();
      
      // Execution order matters
      this.initDatatables();
      this.initHandlers();

      // Start the timer to check job progression
      this.config.checkScanJobsProgresion = setInterval(this.checkJobsProgression.bind(this), 10000);
      // Show alert module new vuknerability
      prestascanSecurity_Tools.moveAlertTop();
    },
    initHandlers : function() {
      var $sel = this.config.jQuerySelectors;
      var $css = this.config.cssSelector;
      this.bindClick($sel.container, $css.btnMainMenuElement, this.handleMenuClick); // Click on the menu item
      this.bindClick($sel.container, $css.btnSubMenuElement, this.handleSubMenuClick); // Click on the sub menu item
      this.bindClick($sel.homeContainer, $css.btnDetailSummaryDashboard, this.handleDashboardLinkDetail); // Click on the links in the dashboard
      this.bindClick($sel.container, $css.buttonReportGenerate, this.handlerGenerateReport); // Click on the scan button
      this.bindClick($sel.updateModuleBtn, null, this.processUpdateModule.bind(window.prestascanSecurity)); // Click on the update button
      this.bindClick($sel.deleteOrDisableModuleBtn, null, this.processDisableOrDeleteModule); // Click to disable or delete a module
      this.bindClick($sel.coreVulnerabilitiesTable, 'td.dt-control', this.processExpandRowDataTable); // Datatable : Click to expand a row
      this.bindClick($sel.dismissAlertBanner, null, this.processDismissAlert); // Click to dismiss module alert banner
      this.bindClick($sel.moreActionAlertModule, null, this.popupMoreActionAlertBanner); // click more action alert module vulnerable
      this.bindClick($sel.cancelJobAction, null, this.processCancelJobAction);  // click cancel job
      this.bindClick($sel.connexionCfgBtn, $css.btnLogOut, this.handleLogOut); // Click on the menu item
      this.bindClick($sel.container, $css.arrowModuleExpand, this.handleCollapsableModuleDetails); // Handle expand and collapsable list
      this.bindClick($sel.container, $css.dismissVulnerability, this.handleActionDismissVulnerability); // click dismiss vulnerability button
      this.bindClick($sel.container, $css.exportScanResults, this.handleActionExportScanResults); // Click on export scan Results
      this.bindClick($sel.refreshSubscription, $css.btnRefreshSubscription, this.handleRefreshSubscription); // Click on the menu item
    },
    bindClick : function (el, sel, handler) {
      el.on('click', sel, handler);
    },
    showModuleUpdatedConfirmationPopup : function() {
      if (typeof module_updated_confirmation_message !== 'undefined' && module_updated_confirmation_message != '') {
        window.prestascanSecurity_Modal.createDialog(module_updated_confirmation_message, []);
      }
    },
    processExpandRowDataTable : function() {
      var tr = $(this).closest('tr');
      var description_text = $(this).closest('tr').find('input.description').val();
      var row = window.prestascanSecurity.config.dataTables.coreVulnerabilitiesDT.row(tr);

      if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('shown');
      } else {
          // Open this row
          row.child(window.prestascanSecurity_Tools.format(description_text)).show();
          tr.addClass('shown');
      }
    },
    processDisableOrDeleteModule : function() {
      var params = {
          action : $(this).data("action"),
          modulename : $(this).data("modulename"),
      };
      var buttons = [
        {
          text : text_yes,
          class: "danger disabled",
          click: "prestascanSecurity.modal_fn_actionModuleUnused",
          additionalparams : params,
        },
        {
          text: text_cancel,
          class: "return",
          click: "prestascanSecurity_Modal.closeDialog",
        }
      ];
      var customHtml = '<label class="chkConfirmModuleUninstall" for="chkConfirmModuleUninstall"><input type="checkbox"> '+checkbox_risk_label+'</label>';
      window.prestascanSecurity_Modal.createDialog(question_to_this_action, buttons, customHtml);
    },
    handleActionModuleUnused : function($obj) {
      var type = $obj.data("action");
      var name = $obj.data("modulename");
      if (!prestascansecurity_isLoggedIn) {
        return false;
      }
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'unusedModulesActions', ajax: true, action_type: type, module_name: name},
        dataType: 'json',
        success: function (response) {
          // Close existing popups
          window.prestascanSecurity_Modal.closeDialog();
          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          }
          if(response.success) {
            if ($('#module-'+name).length) {
              $('#module-'+name).remove();
            }
            $(window).scrollTop(0);
            $("#flash-message").append(
                '<div class="alert alert-success d-print-none" role="alert">\n' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
                '<span aria-hidden="true"><i class="material-icons">close</i></span>\n' +
                '</button>\n' +
                '<div class="alert-text">\n' +
                '<p>'+response.statusText+'</p>\n' +
                '</div>\n' +
                '</div>'
            )
          }
        },
        error: function (response) {
          alert(response.responseJSON.statusText);
        },
        complete: function () {
          return true;
        }
      });
    },
    handlerGenerateReport : function() {
      if (!prestascansecurity_isLoggedIn) {
        var buttons = [{
              text : text_login_btn,
              click: "prestascanSecurity.modal_fn_generateReport",
          }];
        window.prestascanSecurity_Modal.createDialog(text_error_not_logged_in, buttons);
        return;
      }
      window.prestascanSecurity.loadReport($(this).data('action'));
    },
    loadReport : function(action, customData) {
      $(this.config.cssSelector.buttonReportGenerate + "[data-action='"+action+"']").addClass("disabled");
      $('<span class="lds-ring"><span></span><span></span><span></span><span></span></span>').prependTo($(this.config.cssSelector.buttonReportGenerate + "[data-action='"+action+"']"));
      
      if (typeof customData === "undefined") {
        customData = {};
      }
      var data = {
        action : action,
        ajax : true,
      };
      
      Object.assign(data, customData);
      var url = this.config.jQuerySelectors.container.data('urlreports');
      this.postJsonAjax(url, data, this.loadReportSuccess, this.loadReportError);
    },
    loadReportSuccess : function(res) {

      // We reload the page so the message "Scan in progress" is displayed

      if (typeof res.success !== "undefined" && res.success) {
        var jQuerySelectors = window.prestascanSecurity.config.jQuerySelectors;
        // We reload the page to refresh the report
        var currentActiveTab = jQuerySelectors.container.find('li.menu-sous-element.active a:visible').attr('href');
        if (typeof currentActiveTab === "undefined") {
          currentActiveTab = jQuerySelectors.container.find('li.menu_element.active').attr('id');
        }

        if (typeof res.data !== "undefined" && typeof res.data.forceactivetab !== "undefined") {
          // Override active tab if defined in the response
          // (used to redirect the user to the good tab when clicking from the dashboard)
          currentActiveTab = res.data.forceactivetab;
        }

        currentActiveTab = currentActiveTab.replace("#","");
        window.location.href = window.prestascanSecurity_Tools.updateQueryStringParameter(window.location.href, 'activetab', currentActiveTab);

        // Fallback function (we had issues with some specific cases where location.href do not refresh the page)
        setTimeout(function() {
          window.location.reload(true);
        }, 2000);

      } else if (typeof res.error !== "undefined" && res.error && res.statusText !== "undefined" && res.statusText != "") {
        if (res.statusText == 'refresh_module_status_required') {
          window.prestascanSecurity.displayRefreshModuleStatusPopup();
        } else {
          window.prestascanSecurity_Modal.createDialog(res.statusText, []);       
        }
      } else if (typeof res.iterator !== "undefined") {
        // progress
        $(".PrestascanSecurityLoaderProgressPercent").text(Math.ceil(res.iterator.current_step / res.iterator.total_steps * 100));
        $(".PrestascanSecurityLoaderSteps").show();
        // call next
        var customData = {
          step : res.iterator.current_step + 1
        }
        window.prestascanSecurity.loadreports(res.iterator.action, customData);
      }
    },
    loadReportError : function(res) {
      if (res.status === 200) {
        window.prestascanSecurity_Modal.createDialog(res.responseText, []);

      } else {
        window.prestascanSecurity_Modal.createDialog(js_error_occured, []);
      }
    },
    postJsonAjax : function(url, data, handlerSuccess, handlerError) {
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: url,
        dataType: 'json',
        data: data,
        success: handlerSuccess,
        error: handlerError,
        complete: function() {
          $(".btn-generate-report[data-action='"+data.action+"']").removeClass("disabled");
          $(".btn-generate-report[data-action='"+data.action+"'] .lds-ring").remove();
        }
      });
    },
    processUpdateModule : function() {
      clearInterval(this.config.checkScanJobsProgresion);

      $.ajax({
        type: 'GET',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'updateModule', ajax: true},
        dataType: 'json',
        success: function (response) {
          if(response.success) {
            window.location.reload();
          }
          else {
            window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          }
        },
        error: function (response) {
          window.prestascanSecurity_Modal.createDialog(response.statusText, []);
        },
        complete: function () {
          return true;
        }
      });
    },
    checkJobsProgression : function() {

      // This function is called from `setInterval`. Thus, `this` object` is not defined by default.
      // To make sure `this` is defin, make sure to call this function binding `this` context. Such as :
      // `setInterval(this.checkJobsProgression.bind(this), 10000);`
      // You may then use 'this' to access properties or methods of the object

      if (!prestascansecurity_isLoggedIn) {
        return false;
      }

      // Wait for ajax reply to start the sync
      clearInterval(this.config.checkScanJobsProgresion);

      $.ajax({
        type: 'GET',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'checkScanJobsProgression', ajax: true},
        dataType: 'json',
        success: function (response) {
          if (response.error || (response.success && !response.data && response.statusText != '')) {
            // Do not use `this` to access the object in ajax reply
            var buttons = [{
                text : text_reload,
                click : "prestascanSecurity.modal_fn_reload",
            }];
            window.prestascanSecurity_Modal.createDialog(response.statusText, buttons);
          } else if (response.success && typeof response.data != 'undefined' && typeof response.data == 'array') {
            var dt = response.data;
            dt.every(function (tab) {
              if ($("#" + tab + " .scan_in_progress").length == 0) {
                // Do not use `this` to access the object in ajax reply
                window.prestascanSecurity_Modal.createDialog(response.statusText, []);
                return false;
              }
            });
          } else {
            // Do not use `this` to access the object in ajax reply
            window.prestascanSecurity.config.checkScanJobsProgresion = setInterval(
              window.prestascanSecurity.checkJobsProgression.bind(window.prestascanSecurity),
              10000
            );
          }
          
        },
        error: function (response) {
          // Do not use `this` to access the object in ajax reply
          window.prestascanSecurity_Modal.createDialog(response.statusText, []);
        },
        complete: function () {
          return true;
        }
      });

    },
    initDatatables : function()
    {
      var dtParams = {
        bProcessing: true,
        paging: false,
        searching: false,
      };

      var dtParamsFileSize = {
        bProcessing: true,
        paging: false,
        searching: false,
        columnDefs: [
          { type: 'file-size', targets: 1 }
        ],
      };

      if ($('.prestascansecurity_datatable.no-sort-by-file-size').length) {
        $('.prestascansecurity_datatable.no-sort-by-file-size').each(function () {
          if ($(this).attr("id") == "protectionFiles") {
            dtParams.order = [[1, 'asc']];
          }
          $(this).dataTable(dtParams);
        });
      }

      if ($('.prestascansecurity_datatable.sort-by-file-size').length) { 
        $('.prestascansecurity_datatable.sort-by-file-size').dataTable(dtParamsFileSize);
      }  

      $('.prestascansecurity_datatable_custom_ordering' ).each(function( index ) {
        if ($(this).attr('id') === "prestascansecurity_datatable_directorylisting") {
            // Custom sort by Result
            var dtParamsDirectoryListing = { ...dtParams };
            dtParamsDirectoryListing.order = [[3, 'asc']];
            $(this).dataTable(dtParamsDirectoryListing);
        }
      });

      this.config.dataTables.coreVulnerabilitiesDT = $('#coreVulnerabilities').DataTable({
        bProcessing: true,
        paging: false,
        searching: false,
        order: [[2, 'asc']],
      }); 

      const list = document.querySelector('#prestascansecurity_main_container ul#modules');
      if (typeof list !== "undefined" && list !== null) {
        list.addEventListener('scroll', (event) => {
          this.initListingsOverlay();
        });
      }
    },
    initListingsOverlay : function()
    {
      const list = document.querySelector('#prestascansecurity_main_container ul#modules');
      if (typeof list !== "undefined" && list !== null) {
        if (list.scrollHeight - list.scrollTop <= list.offsetHeight) {
          document.querySelector('.scroll-overlay').style.opacity = 0;
        } else {
          document.querySelector('.scroll-overlay').style.opacity = 0.75;
        }
      }
    },
    processDismissAlert : function() {
      var buttons = [
        {
          text : text_yes_dismiss,
          class: "confirm",
          click : "prestascanSecurity.modal_fn_dismissAlert",
        },
        {
          text: text_cancel,
          class: "return",
          click: "prestascanSecurity_Modal.closeDialog",
        }
      ];
      window.prestascanSecurity_Modal.createDialog(question_to_this_dismiss_action, buttons);
    },
    handleActionExportScanResults : function() {
      console.log('handleActionExportScanResults');
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: window.prestascanSecurity.config.jQuerySelectors.container.data('urlreports'),
        data: { action: 'exportScanResults', ajax: true, type: $(this).data("type"), subtype: $(this).data("subtype") },
        dataType : 'json',
        success: function (response) {
            var name = response.data.name;
            var blob = new Blob([response.data.content], { type: 'text/plain' });
            // Create a temporary URL to the Blob
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = name; // Set the desired file name

            // Programmatically click the link to trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        error: function (response) {
          return true;
        }
      });
    },
    handleActionDismissAlert : function (alertId) {
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'dismmissedAlert', ajax: true, alert_id: alertId},
        dataType: 'json',
        success: function (response) {

          window.prestascanSecurity_Modal.closeDialog();

          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          }
          if(response.success) {
            window.location.reload();
            return true;
          }
        },
        error: function (response) {
          window.prestascanSecurity_Modal.closeDialog();
          alert(response.responseJSON.statusText);
          return true;
        }
      });
    },
    popupMoreActionAlertBanner : function()
    {
      var pscan = window.prestascanSecurity;
      var description = $(pscan.config.cssSelector.alertModuleVulnerabilitiesBanner).data('description');
      var iscore = $(pscan.config.cssSelector.alertModuleVulnerabilitiesBanner).data('iscore');
      var more_detail = banner_vulnerability_more_action;
      if (iscore) {
        var more_detail = banner_vulnerability_core_more_action;
      }
      description = "<strong>"+more_detail+"</strong></br></br>"+banner_vulnerability_more_details+"</br>"+description;
      window.prestascanSecurity_Modal.createDialog(description, []);
    },
    handleDashboardLinkDetail : function(e) {
      e.preventDefault();
      var activesubtab = $(this).attr('href');
      var activetab = $(this).parents('.report-result').attr('data-link-parent');
      window.prestascanSecurity_Tools.openMenuTab(activetab, activesubtab);
      return false;
    },
    handleMenuClick : function(e) {
      if ($(this).attr('id') != 'connexion' && $(this).attr('id') != 'contact' && $(this).attr('id') != 'subscription' && $(this).attr('id') != 'refresh_subscription') {
        var activetab = $(this).children("a").first().attr('href');
        $('.menu_element').removeClass('active');
        $('.tab-pane').removeClass('active');
        $(this).addClass('active');
        $(activetab).addClass('active');
        var activesubtab = $(activetab + " .menu-sous-element.active").children("a").first().attr('href');
        $(activesubtab).addClass("active");
        window.prestascanSecurity_Tools.addUrlParameter("activetab",$(this).attr('id'));
      }
    },
    handleSubMenuClick : function(e) {
      e.preventDefault();
      var activesubtab = $(this).attr('href');
      $(this).parent().parent().children('li').removeClass('active');
      $(this).parent().addClass('active');
      window.prestascanSecurity_Tools.addUrlParameter("activetab",$(this).attr('href').replace("#",""));
      $(this).parent().parent().parent().children('div').removeClass('active');
      $(activesubtab).addClass('active');
      return false;
    },
    handleCollapsableModuleDetails : function() {
      window.prestascanSecurity.initListingsOverlay();
      if ($(this).hasClass('arrow-down')) {
        // Slide UP all other modules
        var config = window.prestascanSecurity.config;
        $container = config.jQuerySelectors.container;
        $container.find(".module_block").find(config.cssSelector.arrowModuleExpand).removeClass('arrow-up').addClass('arrow-down').html('&#8964;');
        $container.find(".module_details").slideUp();
        // Slide down current module
        $(this).removeClass('arrow-down').addClass('arrow-up').html('&#8963;');
        $(this).parents('.module_block').find('.module_details').slideDown();
      } else {
        // Already expanded, we want to collapse the current module
        $(this).removeClass('arrow-up').addClass('arrow-down').html('&#8964;');
        $(this).parents('.module_block').find('.module_details').slideUp();
      }
    },
    handleLogOut : function () {
      var buttons = [
        {
          text : text_confirm_log_me_out,
          class: "confirm logout",
          click: "prestascanSecurity.modal_fn_logout",
        },
        {
          text: text_cancel,
          class: "return logout",
          click: "prestascanSecurity_Modal.closeDialog",
        }
      ];
      window.prestascanSecurity_Modal.createDialog(question_to_logout, buttons);
    },
    processCancelJobAction : function(elem)
    {
      if(!prestascansecurity_isLoggedIn) {
        return false;
      }
      var link = elem.currentTarget;
      var action = $(link).attr('data-action');
      var type = $(link).attr('data-type');
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: window.prestascanSecurity.config.jQuerySelectors.container.data('urlreports'),
        data: {action: action, ajax: true, type: type},
        dataType: 'json',
        success: function (response) {
          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          }
          if(response.success) {
            // Do not use `this` to access the object in ajax reply
            var buttons = [
              {
                text : text_reload,
                click : "prestascanSecurity.modal_fn_reload",
              }
            ];
            window.prestascanSecurity_Modal.createDialog(response.statusText, buttons);
          }
        },
        error: function (response) {
          alert(response.responseJSON.statusText);
          return true;
        }
      });
    },
    displayRefreshModuleStatusPopup : function() {
      var buttons = [
        {
          text : text_refresh_status,
          class: "confirm",
          click : "prestascanSecurity.modal_fn_refreshModuleStatus",
        },
        {
          text: text_close,
          class: "return",
          click: "prestascanSecurity_Modal.closeDialog",
        }
      ];
      window.prestascanSecurity_Modal.createDialog(text_refresh_module_status_required, buttons);
    },
    handleRefreshModuleStatus : function () {
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'refreshModuleStatus', ajax: true},
        dataType: 'json',
        success: function (response) {
          if(response.success) {
            window.location.reload();
            return true;
          }
        },
        error: function (response) {
          window.prestascanSecurity_Modal.closeDialog();
          alert(response.responseJSON.statusText);
          return true;
        }
      });
    },
    modal_fn_generateReport : function () {
      // $('.ui-dialog-titlebar-close').trigger( "click" );
      window.prestascanSecurity_Modal.closeDialog();
      openOauthPsScan();
    },
    modal_fn_reload : function () {
      window.location.reload();
    },
    modal_fn_dismissAlert : function () {
      window.prestascanSecurity.handleActionDismissAlert($(window.prestascanSecurity.config.jQuerySelectors.dismissAlertBanner).attr('data-alertId'));
    },
    modal_fn_refreshModuleStatus : function () {
      window.prestascanSecurity.handleRefreshModuleStatus();
    },
    modal_fn_actionModuleUnused : function (e) {
      var $target = $(e.target);
      if ($target.hasClass('validated')) {
        window.prestascanSecurity.handleActionModuleUnused($target);
      }
    },
    modal_fn_logout : function () {
      $.ajax({
        type: 'GET',
        cache: 'false',
        // url: this.config.jQuerySelectors.container.data('urlreports'),
        url: $('#prestascansecurity_main_container').data('urlreports'),
        data: {action: 'logoutUser', ajax: true},
        dataType: 'json',
        success: function (response) {
          if (response.error || (response.success && !response.data && response.statusText != '')) {
            window.prestascanSecurity_Modal.createDialog(response.error, []);
          } else {
            if (response.success) {
              window.location.reload();
            }
          }
        },
        error: function (response) {
          window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          clearInterval(window.prestascanSecurity.config.checkScanJobsProgresion);
        },
        complete: function () {
          return true;
        }
      });
    },
    handleActionDismissVulnerability : function() {
      if (!prestascansecurity_isLoggedIn) {
        var buttons = [{
              text : text_login_btn,
              click: function() {
                $('.ui-dialog-titlebar-close').trigger( "click" );
                openOauthPsScan();
              }
          }];
        window.prestascanSecurity_Tools.createPopupDialog(text_error_not_logged_in, buttons);
        return;
      }
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: window.prestascanSecurity.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'updateDismissedEntitiesList', ajax: true, type: $(this).data("type"), value: $(this).data("value"), subtype: $(this).data("subtype"), action_report: $(this).data("action"), vulnerabilitiesCount: $(this).data("vulnerabilitiescount")},
        dataType: 'json',
        success: function (response) {
          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
          }
          if(response.success) {
            window.location.reload();
            return true;
          }
        },
        error: function (response) {
          window.prestascanSecurity_Tools.closeExistingPopup();
          alert(response.responseJSON.statusText);
          return true;
        }
      });
    },
    handleRefreshSubscription : function() {
      window.prestascanSecurity.refreshSubscription();
    },
    refreshSubscription : function() {
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'refreshSubscription', ajax: true},
        dataType: 'json',
        success: function (response) {
          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Modal.createDialog(response.statusText, []);
          }
          if(response.success) {
            window.location.reload();
            return true;
          }
        },
        error: function (response) {
          window.prestascanSecurity_Modal.closeDialog();
          alert(response.responseJSON.statusText);
          return true;
        }
      });
    },
  } // window.prestascanSecurity
  
  var prestascanSecurity_Tools = window.prestascanSecurity_Tools = {
    format : function(description) {
      return (
        '<table class="coreVulnerabilitiesDesc" cellpadding="9" cellspacing="0" border="0" style="padding-left:50px;">' +
          '<tr>' +
            '<td width="15%" class="vulnerabilitiesDescLabel">'+js_description+' </td>' +
            '<td width="85%" class="vulnerabilitiesDescText">' + description + '</td>' +
          '</tr>' +
        '</table>'
      );
    },
    addUrlParameter : function(name, value) {
      window.history.pushState("","",window.location.href.replace(/[\?&]activetab=[^&]+/, '').replace(/^&/, '?') + "&" + name + "=" + value);
    },
    updateQueryStringParameter : function(uri, key, value) {
      // Credit to @Niyaz : https://stackoverflow.com/questions/5999118/how-can-i-add-or-update-a-query-string-parameter?rq=1
      var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
      var separator = uri.indexOf('?') !== -1 ? "&" : "?";
      if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
      }
      else {
        return uri + separator + key + "=" + value;
      }
    },
    moveAlertTop : function () {
      if (alert_new_modules_vulnerability) {
        // We move it to the top
        var al = $("#prestascansecurity_main_container #alert_vulnerabilities_banner").detach();
        $("#content").find(".bootstrap:nth-child(1)").find(".page-head").first().append(al);
        al.show();
      }
    },
    openMenuTab : function(activetab, activesubtab) {
      $('.menu_element').removeClass('active');
      $('#tab-'+activetab+' .menu-sous-element').removeClass('active'); 
      $('.tab-pane').removeClass('active'); 

      $('#'+activetab).addClass('active');
      $('#tab-'+activetab).addClass('active');
      $(activesubtab).addClass('active');

      activesubtab = activesubtab.replace('#','')
      $('.'+activesubtab+'.menu-sous-element').addClass('active');

      window.prestascanSecurity_Tools.addUrlParameter("activetab", activesubtab);
    },
  } // window.prestascanSecurity_Tools

  prestascanSecurity.init();
});
