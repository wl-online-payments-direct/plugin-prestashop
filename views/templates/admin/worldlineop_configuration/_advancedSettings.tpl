{**
 * 2021 Crédit Agricole
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop / PrestaShop partner
 * @copyright 2020-2021 Crédit Agricole
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
          <!-- Force 3DsV2 -->
          <div class="form-group">
            <label class="control-label col-lg-3 ">
              {l s='Force 3DsV2' mod='worldlineop'}
            </label>
            <div class="col-lg-9">
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
        <h2 class="form-group form-group-h2">{l s='Checkout Flow Modifications' mod='worldlineop'}</h2>
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
        <!-- /Logs -->

        <input type="hidden" name="action" value="saveAdvancedSettingsForm"/>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right" name="submitSaveAdvancedSettingsForm">
        <i class="process-icon-save"></i> {l s='Save' mod='worldlineop'}
      </button>
    </div>
  </form>
</div>
