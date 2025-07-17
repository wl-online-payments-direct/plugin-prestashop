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

        select3DSExemptionType: $('.js-worldlineop-select-3ds-exemption-type-list'),
        input3DSExemption: $('.js-worldlineop-select-3ds-exemption-limit-input'),
        force3DSBlock: $('.js-worldlineop-switch-force-3ds-block'),
        force3DSSwitch: $('.js-worldlineop-switch-force-3ds-switch'),
        force3DSDisabledBlock: $('.js-worldlineop-force-3ds-disabled-block'),

        enforceChallengeBlock: $('.js-worldlineop-enforce-challenge-block'),
        enforceChallengeSwitch: $('.js-worldlineop-enforce-challenge-switch'),
        threeDSExemptionBlock: $('.js-worldlineop-3ds-exemption-block'),
        threeDSExemptionParams: $('.js-worldlineop-3ds-exemption-params'),
        threeDSExemptedTypeHiddenInput: $('#wl-selectedExemptedType'),

        endpointSwitchBlock: $('.js-worldlineop-switch-endpoint-block'),
        endpointSwitchSwitch: $('.js-worldlineop-switch-endpoint-switch'),
        endpointSwitchSettingsBlock: $('.js-worldlineop-switch-endpoint-settings-block'),
        submitFormButton: $('.js-worldlineop-submit-advanced-settings-form'),

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
        el.enforceChallengeBlock.on('click', el.enforceChallengeSwitch, this.toggle3DSExemption);
        this.toggle3DSExemption();
        el.threeDSExemptionBlock.on('click', el.enforceChallengeSwitch, this.toggle3DSExemptionParams);
        this.toggle3DSExemptionParams();
        el.select3DSExemptionType.on('click', this.toggle3DSExemptionType);
        el.input3DSExemption.on('input', this.enter3DSExemptionValue);
        el.force3DSBlock.on('click', el.force3DSSwitch, this.toggle3DSBlock);
        this.toggle3DSBlock();
        this.setExemptionMessage();
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
      toggle3DSBlock: function () {
        if ($('#worldlineopAdvancedSettings_force3DsV2_on').prop('checked')) {
          WorldlineOP.el.force3DSDisabledBlock.show(400);
        } else {
          WorldlineOP.el.force3DSDisabledBlock.hide(200);
        }
      },
      toggle3DSExemption: function () {
        if ($('#worldlineopAdvancedSettings_enforce3DS_on').prop('checked')) {
          WorldlineOP.el.threeDSExemptionBlock.hide(200);
        } else {
          WorldlineOP.el.threeDSExemptionBlock.show(400);
        }
      },
      toggle3DSExemptionParams: function () {
        if ($('#worldlineopAdvancedSettings_threeDSExempted_on').prop('checked')) {
          WorldlineOP.el.threeDSExemptionParams.show(400);
        } else {
          WorldlineOP.el.threeDSExemptionParams.hide(200);
        }
      },
      toggle3DSExemptionType: function (event) {
        var exemptionTypeButtonTextEl = $('.js-worldlineop-select-3ds-exemption-type-button-text');
        var helpTextExemptionLimit30 = $('#js-worldlineop-select-3ds-exemption-limit-30');
        var helpTextExemptionLimit100 = $('#js-worldlineop-select-3ds-exemption-limit-100');

        if (exemptionTypeButtonTextEl && exemptionTypeButtonTextEl[0]) {
          exemptionTypeButtonTextEl[0].innerText = event.target.innerText;
          exemptionTypeButtonTextEl[0].setAttribute('value', event.target.getAttribute('value'));
        }

        if (event.target.getAttribute('value') === 'low-value') {
          WorldlineOP.showElement(helpTextExemptionLimit30);
          WorldlineOP.hideElement(helpTextExemptionLimit100);
        }
        if (event.target.getAttribute('value') === 'transaction-risk-analysis') {
          WorldlineOP.hideElement(helpTextExemptionLimit30);
          WorldlineOP.showElement(helpTextExemptionLimit100);
        }

        WorldlineOP.validate3DSExemptionValue(WorldlineOP.el.input3DSExemption[0], event);
      },
      enter3DSExemptionValue: function (event) {
        if (this.value < 0) {
          this.value = 0;
        }

        WorldlineOP.validate3DSExemptionValue(this, event);
      },
      setExemptionMessage: function() {
        var exemptionTypeButtonTextEl = $('.js-worldlineop-select-3ds-exemption-type-button-text');
        var helpTextExemptionLimit30 = $('#js-worldlineop-select-3ds-exemption-limit-30');
        var helpTextExemptionLimit100 = $('#js-worldlineop-select-3ds-exemption-limit-100');

        if (exemptionTypeButtonTextEl && exemptionTypeButtonTextEl[0]) {
          if (exemptionTypeButtonTextEl[0].getAttribute('value').includes('transaction-risk-analysis')) {
            if (WorldlineOP.el.input3DSExemption && WorldlineOP.el.input3DSExemption[0] &&
                WorldlineOP.el.input3DSExemption[0].getAttribute('value') &&
                WorldlineOP.el.input3DSExemption[0].getAttribute('value') > 0) {
              WorldlineOP.hideElement(helpTextExemptionLimit30);
              WorldlineOP.showElement(helpTextExemptionLimit100);
            }
          }
        }
      },
      validate3DSExemptionValue: function (inputElement, event) {
        var helpTextExemptionLimit30 = $('#js-worldlineop-select-3ds-exemption-limit-30');
        var helpTextExemptionLimit100 = $('#js-worldlineop-select-3ds-exemption-limit-100');
        var helpTextExemptionLimitInvalid = $('#js-worldlineop-select-3ds-exemption-limit-invalid-amount');
        var exemptionTypeButtonTextEl = $('.js-worldlineop-select-3ds-exemption-type-button-text');
        var exemptionLimitAmount = 30;
        var isLimit30Selected = true;
        var shownErrorMessage = null;

        // setting value to be sent when form is submitted.
        if (WorldlineOP.el.threeDSExemptedTypeHiddenInput && WorldlineOP.el.threeDSExemptedTypeHiddenInput[0] && exemptionTypeButtonTextEl && exemptionTypeButtonTextEl[0]) {
          var exemptionTypeButtonValue = exemptionTypeButtonTextEl[0].getAttribute('value') !== '' ?
              exemptionTypeButtonTextEl[0].getAttribute('value') : 'low-value';
          isLimit30Selected = (exemptionTypeButtonValue === 'low-value');
          WorldlineOP.el.threeDSExemptedTypeHiddenInput[0].value = exemptionTypeButtonValue;
        }

        // setting appropriate error message text
        if (helpTextExemptionLimit30 && helpTextExemptionLimit100) {
          exemptionLimitAmount = !isLimit30Selected ? 100 : exemptionLimitAmount;
          if (!helpTextExemptionLimitInvalid[0].innerText.includes(exemptionLimitAmount)) {
            helpTextExemptionLimitInvalid[0].innerText = 'The amount entered is not within the allowed range of 0-' + exemptionLimitAmount + ' EUR.';
          }
          shownErrorMessage = !isLimit30Selected ? helpTextExemptionLimit100 : helpTextExemptionLimit30;
        }

        // first check if exempted type and value are already saved
        let storedExemptedType = document.getElementById('databaseStoredExemptedType')?.value;
        let storedExemptedValue = document.getElementById('databaseStoredExemptedValue')?.value;

        if (storedExemptedType && storedExemptedType === exemptionTypeButtonValue && event.target.tagName.toLowerCase() === 'li') {
          inputElement.value = storedExemptedValue;
          WorldlineOP.disableSaveForExemptionParams(helpTextExemptionLimitInvalid, shownErrorMessage, inputElement);
          return;
        }

        // showing error in case when entered value is greater then limit
        if (inputElement.value > exemptionLimitAmount) {
          if (event.target.tagName.toLowerCase() === 'input') {
            WorldlineOP.enableSaveForExemptionParams(helpTextExemptionLimitInvalid, shownErrorMessage, inputElement);
            return;
          }
          inputElement.value = exemptionLimitAmount;
        }

        WorldlineOP.disableSaveForExemptionParams(helpTextExemptionLimitInvalid, shownErrorMessage, inputElement);
      },
      enableSaveForExemptionParams: function (helpTextExemptionLimitInvalid, shownErrorMessage, inputElement) {
        WorldlineOP.showElement(helpTextExemptionLimitInvalid);
        WorldlineOP.hideElement(shownErrorMessage);
        WorldlineOP.disableElement(WorldlineOP.el.submitFormButton);
        inputElement.classList.add('wl-invalid-input');
      },
      disableSaveForExemptionParams: function (helpTextExemptionLimitInvalid, shownErrorMessage, inputElement) {
        WorldlineOP.hideElement(helpTextExemptionLimitInvalid);
        WorldlineOP.showElement(shownErrorMessage);
        WorldlineOP.enableElement(WorldlineOP.el.submitFormButton);
        inputElement.classList.remove('wl-invalid-input');
      },
      hideElement: function (element) {
        if (element && element[0]) {
          element[0].classList.add('wl-hidden-element');
        }
      },
      showElement: function (element) {
        if (element && element[0]) {
          element[0].classList.remove('wl-hidden-element');
        }
      },
      disableElement: function (element) {
        if (element && element[0]) {
          element[0].setAttribute('disabled', 'disabled');
        }
      },
      enableElement: function (element) {
        if (element && element[0]) {
          element[0].removeAttribute('disabled');
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
