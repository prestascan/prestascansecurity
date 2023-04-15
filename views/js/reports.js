/*
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
        // Home container
        homeContainer : $('#tab-report-home')
      },
      cssSelector : {
        buttonReportGenerate : ".btn-generate-report",
        popupDialog : "#popupDialog",
        alertModuleVulnerabilitiesBanner : "#alert_vulnerabilities_banner",
        btnDetailSummaryDashboard : ".report-result-child a",
        btnMainMenuElement : ".menu_element",
        btnSubMenuElement : ".menu-sous-element a"
      },
      dataTables : {
        coreVulnerabilitiesDT : false
      },
      checkScanJobsProgresion : false
    },
    init : function() {
      console.log("prestascanSecurity: init");
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

      // Click on the menu item
      this.config.jQuerySelectors.container.on(
        'click',
        this.config.cssSelector.btnMainMenuElement,
        this.handleMenuClick
      );

      // Click on the sub menu item
      this.config.jQuerySelectors.container.on(
        'click',
        this.config.cssSelector.btnSubMenuElement,
        this.handleSubMenuClick
      );

      // Click on the links in the dashboard
      this.config.jQuerySelectors.homeContainer.on(
        'click',
        this.config.cssSelector.btnDetailSummaryDashboard,
        this.handleDashboardLinkDetail
      );

      // Click on the scan button
      this.config.jQuerySelectors.container.on(
        'click',
        this.config.cssSelector.buttonReportGenerate,
        this.handlerGenerateReport
      );

      // Click on the update button
      this.config.jQuerySelectors.updateModuleBtn.on(
        'click',
        this.processUpdateModule.bind(window.prestascanSecurity)
      );

      // Click to disable or delete a module
      this.config.jQuerySelectors.deleteOrDisableModuleBtn.on(
        'click',
        this.processDisableOrDeleteModule
      );

      // Datatable : Click to expand a row
      this.config.jQuerySelectors.coreVulnerabilitiesTable.on(
        'click',
        'td.dt-control',
        this.processExpandRowDataTable
      );

      // Click to dismiss module alert banner
      this.config.jQuerySelectors.dismissAlertBanner.on(
          'click',
          this.processDismissAlert
      );

      // click more action alert module vulnerable
      this.config.jQuerySelectors.moreActionAlertModule.on(
          'click',
          this.popupMoreActionAlertBanner
      )
    },
    showModuleUpdatedConfirmationPopup : function() {
      if (typeof module_updated_confirmation_message !== 'undefined' && module_updated_confirmation_message != '') {
        window.prestascanSecurity_Tools.createPopupDialog(module_updated_confirmation_message, []);
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
      var $this = $(this);
      var buttons = [
        {
          text : text_yes,
          class: "confirm",
          click: function() {
            window.prestascanSecurity.handleActionModuleUnused($this);
          }
        },
        {
          text: text_cancel,
          class: "return",
          click:function() {
            $this.dialog("close");
          }
        }
      ];
      window.prestascanSecurity_Tools.createPopupDialog(question_to_this_action, buttons);
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
          window.prestascanSecurity_Tools.closeExistingPopup();
          if(response.error && typeof response.statusText != 'undefined' && response.statusText != '' ) {
            window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
          }
          if(response.success) {
            $obj.parent().parent().parent().parent().remove();
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
        window.prestascanSecurity_Tools.createPopupDialog(text_error_not_logged_in, []);
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
        
      } else if(typeof res.error != "undefined" && res.error && res.statusText != "undefined" && res.statusText != "") {
        window.prestascanSecurity_Tools.createPopupDialog(res.statusText, []);
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
        window.prestascanSecurity_Tools.createPopupDialog(res.responseText, []);
      } else {
        window.prestascanSecurity_Tools.createPopupDialog(js_error_occured, []);
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
            window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
          }
        },
        error: function (response) {
          window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
        },
        complete: function () {
          return true;
        }
      });
    },
    handlerSuccessGetJobsInProgress : function(res) {
      console.log("Success");
      console.log(res);
    },
    handlerErrorGetJobsInProgress : function(res) {
      console.log("Error");
      console.log(res);
    },
    checkJobsProgression : function() {

      // This function is called from `setInterval`. Thus, `this` object` is not defined by default.
      // To make sure `this` is defin, make sure to call this function binding `this` context. Such as :
      // `setInterval(this.checkJobsProgression.bind(this), 10000);`
      // You may then use 'this' to access properties or methods of the object

      if(!prestascansecurity_isLoggedIn) {
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
                click: function() {
                  window.location.reload();
                }
            }];
            window.prestascanSecurity_Tools.createPopupDialog(response.statusText, buttons);
          } else if (response.success && typeof response.data != 'undefined' && typeof response.data == 'array') {
            var dt = response.data;
            dt.every(function (tab) {
              if ($("#" + tab + " .scan_in_progress").length == 0) {
                // Do not use `this` to access the object in ajax reply
                window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
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
          window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
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
          if($(this).attr("id") == "protectionFiles") {
            dtParams.order = [[2, 'desc']];
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
        console.log(list.scrollHeight);
        console.log(list.scrollTop);
        console.log(list.offsetHeight);
        console.log(list.scrollHeight - list.scrollTop <= list.offsetHeight);
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
          click: function() {
            window.prestascanSecurity.handleActionDismissAlert($(window.prestascanSecurity.config.jQuerySelectors.dismissAlertBanner).attr('data-alertId'));
          }
        },
        {
          text: text_cancel,
          class: "return",
          click:function() {
            $(this).dialog("close");
          }
        }
      ];
      window.prestascanSecurity_Tools.createPopupDialog(question_to_this_dismiss_action, buttons);
    },
    handleActionDismissAlert : function (alertId) {
      if(!prestascansecurity_isLoggedIn) {
        return false;
      }
      $.ajax({
        type: 'POST',
        cache: 'false',
        url: this.config.jQuerySelectors.container.data('urlreports'),
        data: {action: 'dismmissedAlert', ajax: true, alert_id: alertId},
        dataType: 'json',
        success: function (response) {

          window.prestascanSecurity_Tools.closeExistingPopup();

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
    popupMoreActionAlertBanner : function()
    {
      var pscan = window.prestascanSecurity;
      var description = $(pscan.config.cssSelector.alertModuleVulnerabilitiesBanner).data('description');
      description = "<strong>"+banner_vulnerability_more_action+"</strong></br></br>"+banner_vulnerability_more_details+"</br>"+description;
      window.prestascanSecurity_Tools.createPopupDialog(description, []);
    },
    handleDashboardLinkDetail : function(e) {
      e.preventDefault();
      var activesubtab = $(this).attr('href');
      var activetab = $(this).parents('.report-result').attr('data-link-parent');
      window.prestascanSecurity_Tools.openMenuTab(activetab, activesubtab);
      return false;
    },
    handleMenuClick : function(e) {
      if ($(this).attr('id') != 'connexion' && $(this).attr('id') != 'contact') {
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
    closeExistingPopup : function() {
      $("#popupDialog").dialog('close');
    },
    createPopupDialog : function(mainContent, buttons) {
      var $popupDialog = $(window.prestascanSecurity.config.cssSelector.popupDialog);
      $popupDialog.attr("title", "");
      $popupDialog.find("*").remove();
      $popupDialog.append("<p>"+mainContent+"</p>");
      $popupDialog.dialog({
        resizable: false,
        height: "auto",
        width: 280,
        modal: true,
        buttons: buttons,
        open: function (event, ui) {
          $(".ui-widget-overlay").css({
            opacity: 0.5,
            filter: "Alpha(Opacity=20)",
            backgroundColor: "black",
            display:"block",
            'z-index': 100
          });
          $(".ui-dialog").css({
            'z-index': 101
          });
        },
        close: function (event, ui) {
            $(".ui-widget-overlay").css({
                display:"none"
            });
        }
      });

    },
  } // window.prestascanSecurity_Tools

  prestascanSecurity.init();
});




















/*
+-+-+-+-+-+-+-+-+-+-+-+-+-+
|S|T|O|P|!|!|!| |C|O|D|I|N|G|
+-+-+-+-+-+-+-+-+-+-+-+-+-+
|B|E|Y|O|N|D| |T|H|I|S| |L|I|N|E|
+-+-+-+-+-+-+-+-+-+-+-+-+-+
*/


// If you need to add code, please add it in the jquery object above.
// No new code below
// Code below will be deleted/converted above.















$(document).ready(function () {
  var $PrestascanSecurity = window.prestascanSecurity.config.jQuerySelectors.container;
  var $arrowModuleDetails = window.prestascanSecurity.config.jQuerySelectors.container.find(".module-details i");
  var $moduleDetails = window.prestascanSecurity.config.jQuerySelectors.container.find(".module_details");
  
  $arrowModuleDetails.each(function(index) {
    $(this).on("click", function(){

      window.prestascanSecurity.initListingsOverlay();
      if ($(this).hasClass('arrow-down')) {
        $arrowModuleDetails.removeClass('arrow-up').addClass('arrow-down');
        $arrowModuleDetails.html('&#8964;');
        $moduleDetails.slideUp();

        $(this).removeClass('arrow-down').addClass('arrow-up');
        $(this).html('&#8963;');
        $(this).parent().parent().parent().find('.module_details').slideDown();
      }
      else {
        $(this).removeClass('arrow-up').addClass('arrow-down');
        $(this).html('&#8964;');
        $(this).parent().parent().parent().find('.module_details').slideUp();
      }
    });
  });


  $("#connexion .logout a").on('click', function () {
    $("#popupDialog").attr("title", "");
    $("#popupDialog *").remove();
    $("#popupDialog").append("<p>"+question_to_logout+"</p>");
    $("#popupDialog").dialog({
      resizable: false,
      height: "auto",
      width: 280,
      modal: true,
      buttons: [
        {
          text : text_confirm_log_me_out,
          class: "confirm",
          click: function() {
             $.ajax({
              type: 'GET',
              cache: 'false',
              // url: this.config.jQuerySelectors.container.data('urlreports'),
              url: $('#prestascansecurity_main_container').data('urlreports'),
              data: {action: 'logoutUser', ajax: true},
              dataType: 'json',
              success: function (response) {
                console.log(response);
                if (response.error || (response.success && !response.data && response.statusText != '')) {
                  window.prestascanSecurity_Tools.createPopupDialog(response.error, []);
                } else {
                  if (response.success) {
                    window.location.reload();
                  }
                }
              },
              error: function (response) {
                window.prestascanSecurity_Tools.createPopupDialog(response.statusText, []);
                clearInterval(window.prestascanSecurity.config.checkScanJobsProgresion);
              },
              complete: function () {
                return true;
              }
            });
          }
        },
        {
          text: text_cancel,
          class: "return",
          click:function() {
              $(this).dialog("close");
          }
        }
      ],
      open: function (event, ui) {
        $(".ui-widget-overlay").css({
          opacity: 0.5,
          filter: "Alpha(Opacity=20)",
          backgroundColor: "black",
          display:"block",
          'z-index': 100
        });
        $(".ui-dialog").css({
          'z-index': 101
        });
      },
      close: function (event, ui) {
          $(".ui-widget-overlay").css({
              display:"none"
          });
      }
    });
  });

});


