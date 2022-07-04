/*
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop partner
 * @copyright 2021 Worldline Online Payments
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

$(document).ready(function () {
  (function () {
    var WorldlineOP = {
      el: {
        body: $('body'),

        envBlock: $('.js-worldlineop-env-block'),
        envSwitch: $('.js-worldlineop-env-switch'),
        envTestBlock: $('.js-worldlineop-env-test-block'),
        envProdBlock: $('.js-worldlineop-env-prod-block'),

        advSettingsBlock: $('.js-worldlineop-advanced-settings-block'),
        advSettingsSwitch: $('.js-worldlineop-advanced-settings-switch'),
        advSettingsTabs: $('.js-tab-advanced'),

        transactionTypeBlock: $('.js-worldlineop-transaction-type-block'),
        transactionTypeSwitch: $('.js-worldlineop-transaction-type-switch'),
        captureBlock: $('.js-worldlineop-capture-delay-block'),

        paymentFlowBlock: $('.js-worldlineop-payment-flow-modifications-block'),
        paymentFlowSwitch: $('.js-worldlineop-payment-flow-modifications-switch'),
        paymentFlowSettingsBlock: $('.js-worldlineop-payment-flow-modifications-settings-block'),

        endpointSwitchBlock: $('.js-worldlineop-switch-endpoint-block'),
        endpointSwitchSwitch: $('.js-worldlineop-switch-endpoint-switch'),
        endpointSwitchSettingsBlock: $('.js-worldlineop-switch-endpoint-settings-block'),

        redirectPaymentDisplayBlock: $('.js-worldlineop-display-redirect-pm-block'),
        redirectPaymentDisplaySwitch: $('.js-worldlineop-display-redirect-pm-switch'),
        redirectPaymentMethodsBlock: $('.js-worldlineop-redirect-payment-methods-block'),

        iframePaymentDisplayBlock: $('.js-worldlineop-display-iframe-pm-block'),
        iframePaymentDisplaySwitch: $('.js-worldlineop-display-iframe-pm-switch'),
        iframePaymentMethodsBlock: $('.js-worldlineop-iframe-payment-methods-block'),

        refreshRedirectPaymentMethodsBtn: $('.js-worldlineop-refresh-redirect-pm-btn'),
        refreshIframePaymentMethodsBtn: $('.js-worldlineop-refresh-iframe-pm-btn'),
        redirectPaymentMethodsList: $('#js-worldlineop-redirect-payment-methods-list'),
        iframePaymentMethodsList: $('#js-worldlineop-iframe-payment-methods-list'),

        whatsNewModal: $('#worldlineop-modal-whatsnew'),

        advSettingsState: $('#worldlineopAdvancedSettings_advancedSettingsEnabled_on').prop('checked'),
      },
      init: function () {
        var el = this.el;

        el.whatsNewModal.on('click', '.js-worldlineop-hide-whatsnew', this.hideWhatsNew);
        el.whatsNewModal.on('shown.bs.modal', this.loadWhatsNew);
        el.whatsNewModal.on('hide.bs.modal', this.resetModal);
        el.envBlock.on('click', el.envSwitch, this.toggleEnv);
        this.toggleEnv();
        el.transactionTypeBlock.on('click', el.transactionTypeSwitch, this.toggleCapture);
        this.toggleCapture();
        el.paymentFlowBlock.on('click', el.paymentFlowSwitch, this.togglePaymentFlow);
        this.togglePaymentFlow();
        el.endpointSwitchBlock.on('click', el.endpointSwitchSwitch, this.toggleEndpoint);
        this.toggleEndpoint();
        el.advSettingsBlock.on('click', el.advSettingsSwitch, this.updateAdvSettings);
        this.toggleAdvSettings();
        el.redirectPaymentDisplayBlock.on('click', el.redirectPaymentDisplaySwitch, this.toggleRedirectPaymentDisplay);
        this.toggleRedirectPaymentDisplay();
        el.iframePaymentDisplayBlock.on('click', el.iframePaymentDisplaySwitch, this.toggleIframePaymentDisplay);
        this.toggleIframePaymentDisplay();
        el.refreshRedirectPaymentMethodsBtn.on('click', this.refreshRedirectPaymentMethods);
        el.refreshIframePaymentMethodsBtn.on('click', this.refreshIframePaymentMethods);
        this.displayWhatsNewModal();
      },
      toggleEnv: function () {
        if ($('#worldlineop-mode-test').prop('checked')) {
          WorldlineOP.el.envTestBlock.show(400);
          WorldlineOP.el.envTestBlock.find('input[type="text"]').attr('required', true);
          WorldlineOP.el.envTestBlock.find('label').addClass('required');
          WorldlineOP.el.envProdBlock.find('input[type="text"]').removeAttr('required');
          WorldlineOP.el.envProdBlock.find('label').removeClass('required');
          WorldlineOP.el.envProdBlock.hide(200);
        } else {
          WorldlineOP.el.envProdBlock.show(400);
          WorldlineOP.el.envProdBlock.find('input[type="text"]').attr('required', true);
          WorldlineOP.el.envProdBlock.find('label').addClass('required');
          WorldlineOP.el.envTestBlock.find('input[type="text"]').removeAttr('required');
          WorldlineOP.el.envTestBlock.find('label').removeClass('required');
          WorldlineOP.el.envTestBlock.hide(200);
        }
      },
      toggleCapture: function () {
        if ($('#worldlineop-type-immediate').prop('checked')) {
          WorldlineOP.el.captureBlock.hide(200);
        } else {
          WorldlineOP.el.captureBlock.show(400);
        }
      },
      togglePaymentFlow: function () {
        if ($('#worldlineopAdvancedSettings_paymentFlowSettingsDisplayed_on').prop('checked')) {
          WorldlineOP.el.paymentFlowSettingsBlock.show(400);
        } else {
          WorldlineOP.el.paymentFlowSettingsBlock.hide(200);
        }
      },
      toggleEndpoint: function () {
        if ($('#worldlineopAdvancedSettings_switchEndpoint_on').prop('checked')) {
          WorldlineOP.el.endpointSwitchSettingsBlock.show(400);
        } else {
          WorldlineOP.el.endpointSwitchSettingsBlock.hide(200);
        }
      },
      toggleAdvSettings: function () {
        if ($('#worldlineopAdvancedSettings_advancedSettingsEnabled_on').prop('checked')) {
          WorldlineOP.el.advSettingsTabs.show();
        } else {
          WorldlineOP.el.advSettingsTabs.hide();
        }
      },
      toggleRedirectPaymentDisplay: function () {
        if ($('#worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on').prop('checked')) {
          WorldlineOP.el.redirectPaymentMethodsBlock.show(400);
        } else {
          WorldlineOP.el.redirectPaymentMethodsBlock.hide(200);
        }
      },
      toggleIframePaymentDisplay: function () {
        if ($('#worldlineopPaymentMethodsSettings_displayIframePaymentOptions_on').prop('checked')) {
          WorldlineOP.el.iframePaymentMethodsBlock.show(400);
        } else {
          WorldlineOP.el.iframePaymentMethodsBlock.hide(200);
        }
      },
      updateAdvSettings: function (e) {
        let advSettingsNewState = $('#worldlineopAdvancedSettings_advancedSettingsEnabled_on').prop('checked');

        if (advSettingsNewState && !WorldlineOP.el.advSettingsState) {
          WorldlineOP.el.advSettingsTabs.show();
          WorldlineOP.el.advSettingsState = true;
        } else if (!advSettingsNewState && WorldlineOP.el.advSettingsState) {
          WorldlineOP.el.advSettingsTabs.hide();
          $('#worldlineop-configuration ul.nav-tabs li').removeClass('active');
          $('#worldlineop-configuration ul.nav-tabs li:first').addClass('active');
          $('#worldlineop-configuration div.tab-content div').removeClass('active');
          $('#worldlineop-configuration div.tab-content div:first').addClass('active');
          WorldlineOP.el.advSettingsState = false;
        } else {
          return;
        }

        var data = {
          controller: 'AdminWorldlineopAjax',
          ajax: 1,
          token: worldlineopAjaxToken,
          action: 'toggleAdvSettings',
          newState: advSettingsNewState,
        };
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: baseAdminDir + 'index.php?' + $.param(data)
        });
      },
      refreshRedirectPaymentMethods: function (e) {
        e.preventDefault();

        var btn = $(this);
        var icon = $(this).find('i');

        btn.toggleClass('disabled');
        icon.toggleClass('icon-refresh icon-spinner icon-spin');
        WorldlineOP.el.redirectPaymentMethodsList.html('');
        var data = {
          controller: 'AdminWorldlineopAjax',
          ajax: 1,
          token: worldlineopAjaxToken,
          type: 'redirect',
          action: 'getPaymentProducts'
        };
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: baseAdminDir + 'index.php?' + $.param(data)
        }).fail(function (jqXHR, textStatus) {
          showErrorMessage(genericErrorMessage);
        }).done(function (data) {
          if (!data.errors) {
            WorldlineOP.el.redirectPaymentMethodsList.html(data.html_result);
          } else {
            showErrorMessage(data.message);
          }
        }).always(function (data) {
          btn.toggleClass('disabled');
          icon.toggleClass('icon-refresh icon-spinner icon-spin');
        });
      },
      refreshIframePaymentMethods: function (e) {
        e.preventDefault();

        var btn = $(this);
        var icon = $(this).find('i');

        btn.toggleClass('disabled');
        icon.toggleClass('icon-refresh icon-spinner icon-spin');
        WorldlineOP.el.iframePaymentMethodsList.html('');
        var data = {
          controller: 'AdminWorldlineopAjax',
          ajax: 1,
          token: worldlineopAjaxToken,
          type: 'iframe',
          action: 'getPaymentProducts'
        };
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: baseAdminDir + 'index.php?' + $.param(data)
        }).fail(function (jqXHR, textStatus) {
          showErrorMessage(genericErrorMessage);
        }).done(function (data) {
          if (!data.errors) {
            WorldlineOP.el.iframePaymentMethodsList.html(data.html_result);
          } else {
            showErrorMessage(data.message);
          }
        }).always(function (data) {
          btn.toggleClass('disabled');
          icon.toggleClass('icon-refresh icon-spinner icon-spin');
        });
      },
      displayWhatsNewModal: function () {
        if (showWhatsNew === true) {
          WorldlineOP.el.whatsNewModal.modal('show');
        }
      },
      loadWhatsNew: function (e) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'index.php',
          data: {
            controller: 'AdminWorldlineopAjax',
            ajax: 1,
            token: worldlineopAjaxToken,
            action: 'whatsNew'
          }
        }).fail(function (jqXHR, textStatus) {
        }).done(function (data) {
          $(e.target).find('.modal-body').html(data.result_html);
        }).always(function (data) {
        });
      },
      hideWhatsNew: function (e) {
        e.preventDefault();

        var btn = $(this);
        var icon = $(this).find('i');

        btn.toggleClass('disabled');
        icon.toggleClass('icon-eye-slash icon-spinner icon-spin');

        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'index.php',
          data: {
            controller: 'AdminWorldlineopAjax',
            ajax: 1,
            token: worldlineopAjaxToken,
            action: 'hideWhatsNew'
          }
        }).fail(function (jqXHR, textStatus) {
        }).done(function (data) {
          WorldlineOP.el.whatsNewModal.modal('hide');
        }).always(function (data) {
          btn.toggleClass('disabled');
          icon.toggleClass('icon-eye-slash icon-spinner icon-spin');
        });
      },
      resetModal: function (e) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'index.php',
          data: {
            controller: 'AdminWorldlineopAjax',
            ajax: 1,
            token: worldlineopAjaxToken,
            action: 'resetModal'
          }
        }).fail(function (jqXHR, textStatus) {
        }).done(function (data) {
          $(e.target).find('.modal-body').remove();
          $(e.target).find('.modal-footer').remove();
          $(e.target).find('.modal-content').append(data.result_html);
        }).always(function (data) {
        });
      }
    };

    WorldlineOP.init();
  })();
});
