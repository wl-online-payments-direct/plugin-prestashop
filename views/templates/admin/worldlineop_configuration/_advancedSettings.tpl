{**
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop / PrestaShop partner
 * @copyright 2020-2021 Worldline Online Payments
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *}

<div class="panel js-worldlineop-advanced-settings-form">
  <form class="form-horizontal"
        action="#"
        name="worldlineop_advanced_settings_form"
        id="worldlineop-advanced-settings-form"
        method="post"
        enctype="multipart/form-data">
    <div class="row">
      <div class="worldlineop-advanced-settings col-xs-12">
        <div class="form-group form-group-h2">
          <h2 class="col-lg-3">{l s='Payment Settings' mod='worldlineop'}</h2>
          <div class="col-lg-9"></div>
        </div>
        <!-- Transaction Type -->
        <div class="form-group js-worldlineop-transaction-type-block">
          <label class="control-label col-lg-3">
            <span class="label-tooltip"
                  data-toggle="tooltip"
                  data-html="true"
                  data-original-title="{l s='Immediate: Authorize & Capture' mod='worldlineop'}<br>{l s='Authorized: Authorize only with pending Capture' mod='worldlineop'}">
              {l s='Transaction type' mod='worldlineop'}
            </span>
          </label>
          <div class="col-lg-9">
            <div class="radio">
              <label>
                <input type="radio"
                       name="worldlineopAdvancedSettings[paymentSettings][transactionType]"
                       id="worldlineop-type-immediate"
                       value="{$data.extra.const.TRANSACTION_TYPE_IMMEDIATE|escape:'html':'UTF-8'}"
                       {if $data.advancedSettings.paymentSettings.transactionType === $data.extra.const.TRANSACTION_TYPE_IMMEDIATE}checked="checked"{/if}>
                {l s='Immediate' mod='worldlineop'}
              </label>
            </div>
            <div class="radio js-worldlineop-transaction-type-switch">
              <label>
                <input type="radio"
                       name="worldlineopAdvancedSettings[paymentSettings][transactionType]"
                       id="worldlineop-type-auth"
                       value="{$data.extra.const.TRANSACTION_TYPE_AUTH|escape:'html':'UTF-8'}"
                       {if $data.advancedSettings.paymentSettings.transactionType != $data.extra.const.TRANSACTION_TYPE_IMMEDIATE}checked="checked"{/if}>
                {l s='Authorized' mod='worldlineop'}
              </label>
            </div>
          </div>
        </div>
        <!-- /Transaction Type -->
        <div class="js-worldlineop-capture-delay-block">
          <!-- Capture Delay -->
          <div class="form-group">
            <label class="control-label col-lg-3 ">
          <span>
            {l s='Delay before payment capture' mod='worldlineop'}
          </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][captureDelay]" class="fixed-width-md">
                {for $day=$data.extra.const.CAPTURE_DELAY_MIN to $data.extra.const.CAPTURE_DELAY_MAX}
                  <option value="{$day|intval}"
                          {if $data.advancedSettings.paymentSettings.captureDelay == $day}selected{/if}>
                    {if $day === 0}
                      {l s='Manual capture' mod='worldlineop'}
                    {elseif $day === 1}
                      {$day|intval} {l s='day' mod='worldlineop'}
                    {else}
                      {$day|intval} {l s='days' mod='worldlineop'}
                    {/if}
                  </option>
                {/for}
              </select>
            </div>
            <div class="col-lg-9 col-lg-offset-3">
              <div class="help-block">
                {l s='Number of days before triggering automatic payment capture' mod='worldlineop'}
                <span></span>
              </div>
            </div>
          </div>
          <!-- /Capture Delay -->
          <!-- Capture cronjob -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>
                {l s='Capture cronjob' mod='worldlineop'}
              </span>
            </label>
            <div class="col-lg-9">
              <p class="form-control-static">{l s='Cron command example to run capture process 4 times a day:' mod='worldlineop'}</p>
              <p><code>{$data.extra.path.controllers.captureCron|escape:'htmlall':'UTF-8'}</code></p>
            </div>
          </div>
          <!-- /Capture cronjob -->
        </div>
        <!-- Logs -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            {l s='Enable advanced logging' mod='worldlineop'}
          </label>
          <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[logsEnabled]"
                     id="worldlineopAdvancedSettings_logsEnabled_on"
                     {if $data.advancedSettings.logsEnabled === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_logsEnabled_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[logsEnabled]"
                     id="worldlineopAdvancedSettings_logsEnabled_off"
                     {if $data.advancedSettings.logsEnabled != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_logsEnabled_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='The minimum log level will be set to Debug.' mod='worldlineop'}
              {l s='Older files can be accessed on your server, in the "logs" directory of this module.' mod='worldlineop'}
              <br/>
              <a href="{$link->getAdminLink('AdminWorldlineopLogs', true, [], ['action' => 'downloadLogFile'])|escape:'html':'UTF-8'}">
                {l s='Click here to download the latest file' mod='worldlineop'}
              </a>
              <span></span>
            </div>
          </div>
        </div>
        <!-- /Logs -->

        <!-- Payment Flow Modifications -->
        <div class="form-group form-group-h2 js-worldlineop-payment-flow-modifications-block">
          <h2 class="col-lg-3">{l s='Payment Flow Modifications' mod='worldlineop'}</h2>
          <div class="col-lg-9 js-worldlineop-payment-flow-modifications-switch">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[paymentFlowSettingsDisplayed]"
                     id="worldlineopAdvancedSettings_paymentFlowSettingsDisplayed_on"
                     {if $data.advancedSettings.paymentFlowSettingsDisplayed === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_paymentFlowSettingsDisplayed_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[paymentFlowSettingsDisplayed]"
                     id="worldlineopAdvancedSettings_paymentFlowSettingsDisplayed_off"
                     {if $data.advancedSettings.paymentFlowSettingsDisplayed != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_paymentFlowSettingsDisplayed_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
        </div>
        <!-- /Payment Flow Modifications -->

        <div class="js-worldlineop-payment-flow-modifications-settings-block">
          <div class="alert alert-info">
            {l s='We recommend you to use the default settings unless absolutely necessary' mod='worldlineop'}
          </div>
          <!-- Payment accepted status mapping -->
          <div class="form-group">
            <label class="control-label col-lg-3" for="worldlineopAdvancedSettings[paymentSettings][successOrderStateId]">
            <span>
              {l s='Payment accepted status mapping' mod='worldlineop'}
            </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][successOrderStateId]" class="fixed-width-xxl">
                {foreach $data.extra.statuses as $status}
                  <option value="{$status.id_order_state|intval}"
                          {if $status.id_order_state == $data.advancedSettings.paymentSettings.successOrderStateId}selected{/if}>
                    {$status.name|escape:'html':'UTF-8'}
                    {if $status.id_order_state == $data.extra.defaultStatuses.PS_OS_PAYMENT}{l s='(default)' mod='worldlineop'}{/if}
                  </option>
                {/foreach}
              </select>
            </div>
          </div>
          <!-- /Payment accepted status mapping -->
          <!-- Payment error status mapping -->
          <div class="form-group">
            <label class="control-label col-lg-3" for="worldlineopAdvancedSettings[paymentSettings][successOrderStateId]">
            <span>
              {l s='Payment error status mapping' mod='worldlineop'}
            </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][errorOrderStateId]" class="fixed-width-xxl">
                {foreach $data.extra.statuses as $status}
                  <option value="{$status.id_order_state|intval}"
                          {if $status.id_order_state == $data.advancedSettings.paymentSettings.errorOrderStateId}selected{/if}>
                    {$status.name|escape:'html':'UTF-8'}
                    {if $status.id_order_state == $data.extra.defaultStatuses.PS_OS_ERROR}{l s='(default)' mod='worldlineop'}{/if}
                  </option>
                {/foreach}
              </select>
            </div>
          </div>
          <!-- /Payment error status mapping -->
          <!-- Payment pending status mapping -->
          <div class="form-group">
            <label class="control-label col-lg-3" for="worldlineopAdvancedSettings[paymentSettings][successOrderStateId]">
            <span>
              {l s='Pending payment status mapping' mod='worldlineop'}
            </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][pendingOrderStateId]" class="fixed-width-xxl">
                {foreach $data.extra.statuses as $status}
                  <option value="{$status.id_order_state|intval}"
                          {if $status.id_order_state == $data.advancedSettings.paymentSettings.pendingOrderStateId}selected{/if}>
                    {$status.name|escape:'html':'UTF-8'}
                    {if $status.id_order_state == $data.extra.defaultStatuses.WOP_PENDING_ORDER_STATUS_ID}{l s='(default)' mod='worldlineop'}{/if}
                  </option>
                {/foreach}
              </select>
            </div>
          </div>
          <!-- /Payment pending status mapping -->
          <!-- Safety Delay -->
          <div class="form-group">
            <label class="control-label col-lg-3" for="worldlineopAdvancedSettings[paymentSettings][safetyDelay]">
          <span>
            {l s='Order validation safety delay' mod='worldlineop'}
          </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][safetyDelay]" class="fixed-width-md">
                {for $seconds=$data.extra.const.SAFETY_DELAY_MIN to $data.extra.const.SAFETY_DELAY_MAX}
                  <option value="{$seconds|intval}"
                          {if $data.advancedSettings.paymentSettings.safetyDelay == $seconds}selected{/if}>
                    {$seconds|intval} {l s='seconds' mod='worldlineop'}
                  </option>
                {/for}
              </select>
            </div>
            <div class="col-lg-9 col-lg-offset-3">
              <div class="help-block">
                {l s='If you use the split order feature, activate this option to gracefully handle the duplication of the order by retaining any incoming webhook for the determined period' mod='worldlineop'}
              </div>
            </div>
          </div>
          <!-- /Safety Delay -->
          <!-- Retention Delay -->
          <div class="form-group">
            <label class="control-label col-lg-3" for="worldlineopAdvancedSettings[paymentSettings][retentionHours]">
            <span>
              {l s='Release inventory from Pending payment orders after' mod='worldlineop'}
            </span>
            </label>
            <div class="col-lg-9">
              <select name="worldlineopAdvancedSettings[paymentSettings][retentionHours]" class="fixed-width-md">
                {for $hours=$data.extra.const.RETENTION_DELAY_MIN to $data.extra.const.RETENTION_DELAY_MAX}
                  {if $hours % 3 === 0}
                    <option value="{$hours|intval}"
                            {if $data.advancedSettings.paymentSettings.retentionHours == $hours}selected{/if}>
                      {$hours|intval} {l s='hours' mod='worldlineop'}
                    </option>
                  {/if}
                {/for}
              </select>
            </div>
          </div>
          <!-- /Retention Delay -->
          <!-- Pending cronjob -->
          <div class="form-group">
            <label class="control-label col-lg-3">
            <span>
              {l s='Pending cronjob' mod='worldlineop'}
            </span>
            </label>
            <div class="col-lg-9">
              <p class="form-control-static">{l s='Cron command example to run process every hour:' mod='worldlineop'}</p>
              <p><code>{$data.extra.path.controllers.pendingCron|escape:'htmlall':'UTF-8'}</code></p>
            </div>
          </div>
          <!-- /Pending cronjob -->
          <!-- Switch Endpoint -->
          <div class="form-group form-group-h2 js-worldlineop-switch-endpoint-block">
            <label class="control-label col-lg-3 ">{l s='Switch endpoint' mod='worldlineop'}</label>
            <div class="col-lg-9 js-worldlineop-switch-endpoint-switch">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[switchEndpoint]"
                     id="worldlineopAdvancedSettings_switchEndpoint_on"
                     {if $data.advancedSettings.switchEndpoint === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_switchEndpoint_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[switchEndpoint]"
                     id="worldlineopAdvancedSettings_switchEndpoint_off"
                     {if $data.advancedSettings.switchEndpoint != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_switchEndpoint_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
            </div>
          </div>
          <!-- /Switch Endpoint -->

          <div class="js-worldlineop-switch-endpoint-settings-block">
            <!-- Test Endpoint -->
            <div class="form-group">
              <label class="control-label col-lg-3">
                <span>{l s='Test Endpoint' mod='worldlineop'}</span>
              </label>
              <div class="col-lg-9">
                <div class="fixed-width-xxl">
                  <input value="{$data.advancedSettings.testEndpoint|escape:'htmlall':'UTF-8'}"
                         type="text"
                         name="worldlineopAdvancedSettings[testEndpoint]"
                         class="input fixed-width-xxl">
                </div>
              </div>
            </div>
            <!-- /Test Endpoint -->
            <!-- Prod Endpoint -->
            <div class="form-group">
              <label class="control-label col-lg-3">
                <span>{l s='Prod Endpoint' mod='worldlineop'}</span>
              </label>
              <div class="col-lg-9">
                <div class="fixed-width-xxl">
                  <input value="{$data.advancedSettings.prodEndpoint|escape:'htmlall':'UTF-8'}"
                         type="text"
                         name="worldlineopAdvancedSettings[prodEndpoint]"
                         class="input fixed-width-xxl">
                </div>
              </div>
            </div>
            <!-- /Prod Endpoint -->
          </div>
        </div>
        <div class="form-group form-group-h2">
          <h2 class="col-lg-3">{l s='Checkout Flow Modifications' mod='worldlineop'}</h2>
          <div class="col-lg-9"></div>
        </div>
        <!-- Group cards -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            {l s='Group payment options by card' mod='worldlineop'}
          </label>
          <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[groupCardPaymentOptions]"
                     id="worldlineopAdvancedSettings_groupCardPaymentOptions_on"
                     {if $data.advancedSettings.groupCardPaymentOptions === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_groupCardPaymentOptions_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[groupCardPaymentOptions]"
                     id="worldlineopAdvancedSettings_groupCardPaymentOptions_off"
                     {if $data.advancedSettings.groupCardPaymentOptions != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_groupCardPaymentOptions_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='Only for the generic payment option. If you choose to group payment options by card, the customer will have one unique choice for cards instead of x choices.' mod='worldlineop'}
              <span></span>
            </div>
          </div>
        </div>
        <!-- Omit item details -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            <span class="label-tooltip"
                  data-toggle="tooltip"
                  data-html="true"
                  data-original-title="{l s='When enabled, customers\' order item details—such as product names, prices, and quantities—are omitted from payment requests. This may be useful to enhance order privacy or address compatibility issues with third-party plugins affecting order details. However, be aware that excluding item details may hinder certain risk assessments done by financial institutions, and payment methods that require them (e.g., Klarna) may not be presented to your customers.' mod='worldlineop'}">
            {l s='Omit order item details' mod='worldlineop'}
          </label>
          <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[omitOrderItemDetails]"
                     id="worldlineopAdvancedSettings_omitOrderItemDetails_on"
                     {if $data.advancedSettings.omitOrderItemDetails === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_omitOrderItemDetails_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[omitOrderItemDetails]"
                     id="worldlineopAdvancedSettings_omitOrderItemDetails_off"
                     {if $data.advancedSettings.omitOrderItemDetails != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_omitOrderItemDetails_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='When enabled, order item details will not be included in payment requests. Only the total order amount will be transmitted.' mod='worldlineop'}
              <span></span>
            </div>
          </div>
        </div>
        <!-- /Group cards -->
        <div class="form-group form-group-h2">
          <div class="col-lg-3"></div>
          <h4 class="col-lg-9">{l s='UE specific settings' mod='worldlineop'}</h4>
        </div>
        <!-- Force 3DsV2 -->
        <div class="form-group js-worldlineop-switch-force-3ds-block">
          <label class="control-label col-lg-3 ">
            {l s='Enable 3Ds' mod='worldlineop'}
          </label>
          <div class="col-lg-9 js-worldlineop-switch-force-3ds-switch">
              <span class="switch prestashop-switch fixed-width-sm">
                <input type="radio"
                       value="1"
                       name="worldlineopAdvancedSettings[force3DsV2]"
                       id="worldlineopAdvancedSettings_force3DsV2_on"
                       {if $data.advancedSettings.force3DsV2 === true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_force3DsV2_on">{l s='Yes' mod='worldlineop'}</label>
                <input type="radio"
                       value="0"
                       name="worldlineopAdvancedSettings[force3DsV2]"
                       id="worldlineopAdvancedSettings_force3DsV2_off"
                       {if $data.advancedSettings.force3DsV2 != true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_force3DsV2_off">{l s='No' mod='worldlineop'}</label>
                <a class="slide-button btn"></a>
              </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='It is mandatory to enforce 3DsV2 in Europe, but can be turned off for other geographies' mod='worldlineop'}
            </div>
          </div>
        </div>
        <!-- /Force 3DsV2 -->
        <div class="js-worldlineop-force-3ds-disabled-block">
          <!-- Mandatory 3DS -->
          <div class="form-group js-worldlineop-enforce-challenge-block">
            <label class="control-label col-lg-3 ">
              {l s='Enable mandatory 3DS' mod='worldlineop'}
            </label>
            <div class="col-lg-9 js-worldlineop-enforce-challenge-switch">
              <span class="switch prestashop-switch fixed-width-sm">
                <input type="radio"
                       value="1"
                       name="worldlineopAdvancedSettings[enforce3DS]"
                       id="worldlineopAdvancedSettings_enforce3DS_on"
                       {if $data.advancedSettings.enforce3DS === true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_enforce3DS_on">{l s='Yes' mod='worldlineop'}</label>
                <input type="radio"
                       value="0"
                       name="worldlineopAdvancedSettings[enforce3DS]"
                       id="worldlineopAdvancedSettings_enforce3DS_off"
                       {if $data.advancedSettings.enforce3DS != true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_enforce3DS_off">{l s='No' mod='worldlineop'}</label>
                <a class="slide-button btn"></a>
              </span>
            </div>
            <div class="col-lg-9 col-lg-offset-3">
              <div class="help-block">
              </div>
            </div>
          </div>
          <!-- /Mandatory 3DS -->
          <!-- 3DS Exemption -->
          <div class="form-group js-worldlineop-3ds-exemption-block">
            <label class="control-label col-lg-3 ">
               <span class="label-tooltip"
                     data-toggle="tooltip"
                     data-html="true"
                     data-original-title="{l s='By enabling the 3DS Exemption, you allow the card issuer and your acquirer to evaluate the risk level of transactions up to the Exemption Limit you specify in EUR. If the criteria are met, your customers will be exempt from Strong Customer Authentication (SCA).' mod='worldlineop'}<br>{l s='If your acquirer rejects the exemption and requires SCA, this is referred to as a soft decline. In such cases, we will attempt to prompt the customer for SCA and reinitiate the transaction if the customer successfully authenticates.' mod='worldlineop'}">
                {l s='Exempt transactions from 3DS' mod='worldlineop'}
              </span>
            </label>
            <div class="col-lg-9">
              <span class="switch prestashop-switch fixed-width-sm">
                <input type="radio"
                       value="1"
                       name="worldlineopAdvancedSettings[threeDSExempted]"
                       id="worldlineopAdvancedSettings_threeDSExempted_on"
                       {if $data.advancedSettings.threeDSExempted === true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_threeDSExempted_on">{l s='Yes' mod='worldlineop'}</label>
                <input type="radio"
                       value="0"
                       name="worldlineopAdvancedSettings[threeDSExempted]"
                       id="worldlineopAdvancedSettings_threeDSExempted_off"
                       {if $data.advancedSettings.threeDSExempted != true}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_threeDSExempted_off">{l s='No' mod='worldlineop'}</label>
                <a class="slide-button btn"></a>
              </span>
            </div>
            <div class="col-lg-9 col-lg-offset-3 wl-margin-bottom-15">
              <div class="help-block">
                {l s='When enabled, transactions with an order amount < exemption limit value will be exempted from 3DS' mod='worldlineop'}
                <span></span>
              </div>
            </div>
            <div class="exemption-type js-worldlineop-3ds-exemption-params">
              <div class="alert alert-warning">
                <p class="text-info">
                  {l s='Please be aware that if fraud occurs on a transaction that has been granted an exemption, the liability falls on the merchant!' mod='worldlineop'}
                </p>
              </div>
              <label class="control-label col-lg-3 "
                     for="wlToggleButton">
                <span class="label-tooltip"
                      data-toggle="tooltip"
                      data-html="true"
                      data-original-title="{l s='The Low-Value exemption is suitable for transactions below the specified monetary threshold of 30 EUR, allowing these low-value transactions a change to bypass Strong Customer Authentication (SCA) and streamline the checkout process.' mod='worldlineop'}
                      <br>{l s='On the other hand, the Transaction-Risk-Analysis exemption enables a dynamic risk assessment for your transactions. The card issuer and your acquirer will evaluate the transaction based on various risk factors, allowing transactions up to a limit of 100 EUR to qualify for exemption if they are deemed low risk.' mod='worldlineop'}
                      <br>{l s='Make your selection carefully to optimize the balance between customer experience and security for your transactions.' mod='worldlineop'}">
                  {l s='Exemption type' mod='worldlineop'}
                </span>
              </label>
              <div class="col-lg-9">
                <button type="button"
                        class="btn btn-default wl-dropdown-toggle"
                        tabindex="-1"
                        name="wlToggleButton"
                        data-toggle="dropdown">
                                    <span class="js-worldlineop-select-3ds-exemption-type-button-text"
                                          value="{$data.advancedSettings.threeDSExemptedType|default:'low-value'|escape:'htmlall':'UTF-8'}">
                                        {if $data.advancedSettings.threeDSExemptedType == 'low-value' || !$data.advancedSettings.threeDSExemptedType}
                                          {l s='low-value (default)' mod='worldlineop'}
                                        {else}
                                          {$data.advancedSettings.threeDSExemptedType|escape:'htmlall':'UTF-8'}
                                        {/if}
                                    </span>
                  <i class="icon-caret-down"></i>
                </button>
                <input type="hidden" name="worldlineopAdvancedSettings[threeDSExemptedType]"
                       value="{$data.advancedSettings.threeDSExemptedType|default:'low-value'|escape:'htmlall':'UTF-8'}"
                       id="wl-selectedExemptedType">
                <ul class="dropdown-menu exemption-types-list js-worldlineop-select-3ds-exemption-type-list">
                  <li value="low-value">
                    {l s='low-value (default)' mod='worldlineop'}
                  </li>
                  <li value="transaction-risk-analysis">
                    {l s='transaction-risk-analysis' mod='worldlineop'}
                  </li>
                </ul>
              </div>
              <div class="col-lg-9 col-lg-offset-3">
                <div class="help-block">
                  {l s='Please select the exemption type.' mod='worldlineop'}
                  <span></span>
                </div>
              </div>
              <input type="hidden" id="databaseStoredExemptedValue" value="{$data.advancedSettings.threeDSExemptedValue|default:0|escape:'htmlall':'UTF-8'}"/>
              <input type="hidden" id="databaseStoredExemptedType" value="{$data.advancedSettings.threeDSExemptedType|escape:'htmlall':'UTF-8'}"/>
              <label class="control-label col-lg-3 "
                     for="worldlineopAdvancedSettings[threeDSExemptedValue]">
                 <span class="label-tooltip"
                       data-toggle="tooltip"
                       data-html="true"
                       data-original-title="{l s='The exemption limit is specified in EUR. Therefore, if the checkout currency is not in EUR, the conversion will depend on the exchange rate to EUR. It is essential to have EUR configured in your environment, and to ensure accurate conversions, please keep your exchange rates updated.' mod='worldlineop'}
                        <br>{l s='If EUR is not configured, the 3DS exemption will not be applied, and the transaction will not have the exemption requested.' mod='worldlineop'}
                        <br><br>{l s='Additional Note: PSD2 designates the EUR as the base currency for determining exemption limits for other currencies in the EEA. However, each region can decide to adapt these limits, and regions outside the EEA may also support exemption requests. Due to these complexities, we cannot guarantee that the exemption request will be considered by the issuer or your acquirer.' mod='worldlineop'}">
                  {l s='Exemption limit' mod='worldlineop'}
                </span>
              </label>
              <div class="col-lg-9">
                <input value="{$data.advancedSettings.threeDSExemptedValue|default:0|escape:'htmlall':'UTF-8'}"
                       class="wl-exempt-type-input js-worldlineop-select-3ds-exemption-limit-input"
                       type="number" name="worldlineopAdvancedSettings[threeDSExemptedValue]"
                       min="0"
                       required
                />
              </div>
              <div class="col-lg-9 col-lg-offset-3">
                <div id="js-worldlineop-select-3ds-exemption-limit-30" class="help-block">
                  {l s='Please enter the amount limit (0 to 30 EUR).' mod='worldlineop'}
                  <span></span>
                </div>
                <div id="js-worldlineop-select-3ds-exemption-limit-100" class="help-block wl-hidden-element">
                  {l s='Please enter the amount limit (0 to 100 EUR).' mod='worldlineop'}
                  <span></span>
                </div>
                <div id="js-worldlineop-select-3ds-exemption-limit-invalid-amount" class="help-block wl-hidden-element">
                </div>
              </div>
            </div>
          </div>
          <!-- /3DS Exemption -->
        </div>
        <div class="form-group form-group-h2">
          <div class="col-lg-3"></div>
          <h4 class="col-lg-9">{l s='Non-UE specific settings' mod='worldlineop'}</h4>
        </div>
        <!-- Surcharging -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            {l s='Enable surcharging' mod='worldlineop'}
          </label>
          <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-sm">
              <input type="radio"
                     value="1"
                     name="worldlineopAdvancedSettings[surchargingEnabled]"
                     id="worldlineopAdvancedSettings_surchargingEnabled_on"
                     {if $data.advancedSettings.surchargingEnabled === true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_surchargingEnabled_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopAdvancedSettings[surchargingEnabled]"
                     id="worldlineopAdvancedSettings_surchargingEnabled_off"
                     {if $data.advancedSettings.surchargingEnabled != true}checked="checked"{/if}>
              <label for="worldlineopAdvancedSettings_surchargingEnabled_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='When surcharging is enabled, extra fees related to credit card organizations will be supported by your customers' mod='worldlineop'}
              <span></span>
            </div>
          </div>
        </div>
        <!-- /Group cards -->

        <input type="hidden" name="action" value="saveAdvancedSettingsForm"/>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right js-worldlineop-submit-advanced-settings-form" name="submitSaveAdvancedSettingsForm">
        <i class="process-icon-save"></i> {l s='Save' mod='worldlineop'}
      </button>
    </div>
  </form>
</div>

