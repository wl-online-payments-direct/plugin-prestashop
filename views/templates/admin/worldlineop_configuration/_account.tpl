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

<div class="panel js-worldlineop-account-form">
  <form class="form-horizontal"
        action="#"
        name="worldlineop_account_form"
        id="worldlineop-account-form"
        method="post"
        enctype="multipart/form-data">
    <div class="row">
      <div class="worldlineop-account col-xs-12">
        <!-- Environment -->
        <div class="form-group js-worldlineop-env-block">
          <label class="control-label col-lg-3">
            <span>{l s='Environment' mod='worldlineop'}</span>
          </label>
          <div class="col-lg-9 js-worldlineop-env-switch">
            <div class="radio">
              <label>
                <input type="radio"
                       name="worldlineopAccountSettings[environment]"
                       id="worldlineop-mode-test"
                       value="{$data.extra.const.ACCOUNT_MODE_TEST|escape:'html':'UTF-8'}"
                       {if $data.accountSettings.environment != $data.extra.const.ACCOUNT_MODE_PROD}checked="checked"{/if}>
                {l s='Test' mod='worldlineop'}
              </label>
            </div>
            <div class="radio">
              <label>
                <input type="radio"
                       name="worldlineopAccountSettings[environment]"
                       id="worldlineop-mode-prod"
                       value="{$data.extra.const.ACCOUNT_MODE_PROD|escape:'html':'UTF-8'}"
                       {if $data.accountSettings.environment == $data.extra.const.ACCOUNT_MODE_PROD}checked="checked"{/if}>
                {l s='Production' mod='worldlineop'}
              </label>
            </div>
          </div>
        </div>
        <!-- /Environment -->
        <div class="js-worldlineop-env-test-block">
          <h2 class="col-lg-offset-3 col-lg-9">{l s='Test credentials' mod='worldlineop'}</h2>
          <!-- Test PSPID -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Test PSPID' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.testPspid|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[testPspid]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Test PSPID -->
          <div class="alert alert-info">
            <p class="text-info">
              {l s='To retrieve the API Key and API secret in your PSPID, follow these steps:' mod='worldlineop'}
            </p>
            <p class="text-info">
              {l s='> Login to the Back Office. Go to Configuration > Technical information > Ingenico Direct Settings > Direct API Key' mod='worldlineop'}<br>
              {l s='> If you have not configured anything yet, the screen shows "No API credentials found". To create both API Key and API Secret click on "GENERATE"' mod='worldlineop'}
            </p>
          </div>
          <!-- Test API Key -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Test API Key' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.testApiKey|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[testApiKey]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Test API Key -->
          <!-- Test API Secret -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Test API Secret' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.testApiSecret|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[testApiSecret]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Test API Secret -->
          <div class="alert alert-info">
            <p class="text-info">
              {l s='To retrieve the webhooks credentials, login to the Back Office.' mod='worldlineop'}<br>
              {l s='Go to Configuration > Technical information > Ingenico Direct settings > Webhooks Configuration and perform the following steps:' mod='worldlineop'}
            </p>
            <p class="text-info">
              {l s='> Click on "GENERATE WEBHOOKS API KEY"' mod='worldlineop'}<br>
              {l s='> Copy & Paste the WebhooksKeySecret immediately' mod='worldlineop'}<br>
              {l s='> In "Endpoints URLs", paste the Webhooks URL of your store - see below' mod='worldlineop'}<br>
              {l s='> Click on "SAVE" to confirm your settings' mod='worldlineop'}
            </p>
            <p>
              <i class="icon icon-warning"></i>
              {l s='If you have several shops & different credentials, please configure your Worldline portals for each shops/accounts.' mod='worldlineop'}
            </p>
          </div>
          <!-- Test Webhooks Key -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Test Webhooks Key' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.testWebhooksKey|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[testWebhooksKey]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Test Webhooks Key -->
          <!-- Test Webhooks Secret -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Test Webhooks Secret' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.testWebhooksSecret|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[testWebhooksSecret]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
        </div>
        <!-- /Test Webhooks Secret -->
        <div class="js-worldlineop-env-prod-block">
          <h2 class="col-lg-offset-3 col-lg-9">{l s='Production credentials' mod='worldlineop'}</h2>
          <!-- Prod PSPID -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Prod PSPID' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.prodPspid|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[prodPspid]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Prod PSPID -->
          <div class="alert alert-info">
            <p class="text-info">
              {l s='To retrieve the API Key and API secret in your PSPID, follow these steps:' mod='worldlineop'}
            </p>
            <p class="text-info">
              {l s='> Login to the Back Office. Go to Configuration > Technical information > Ingenico Direct Settings > Direct API Key' mod='worldlineop'}<br>
              {l s='> If you have not configured anything yet, the screen shows "No API credentials found". To create both API Key and API Secret click on "GENERATE"' mod='worldlineop'}
            </p>
          </div>
          <!-- Prod API Key -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Prod API Key' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.prodApiKey|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[prodApiKey]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Prod API Key -->
          <!-- Prod API Secret -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Prod API Secret' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.prodApiSecret|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[prodApiSecret]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Prod API Secret -->
          <div class="alert alert-info">
            <p class="text-info">
              {l s='To retrieve the webhooks credentials, login to the Back Office.' mod='worldlineop'}<br>
              {l s='Go to Configuration > Technical information > Ingenico Direct settings > Webhooks Configuration and perform the following steps:' mod='worldlineop'}
            </p>
            <p class="text-info">
              {l s='> Click on "GENERATE WEBHOOKS API KEY"' mod='worldlineop'}<br>
              {l s='> Copy & Paste the WebhooksKeySecret immediately' mod='worldlineop'}<br>
              {l s='> In "Endpoints URLs", paste the Webhooks URL of your store - see below' mod='worldlineop'}<br>
              {l s='> Click on "SAVE" to confirm your settings' mod='worldlineop'}
            </p>
            <p class="text-info">
              <i class="icon icon-warning"></i>
              {l s='If you have several shops & different credentials, please configure your Worldline portals for each shops/accounts.' mod='worldlineop'}
            </p>
          </div>
          <!-- Prod Webhooks Key -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Prod Webhooks Key' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.prodWebhooksKey|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[prodWebhooksKey]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Prod Webhooks Key -->
          <!-- Prod Webhooks Secret -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Prod Webhooks Secret' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.accountSettings.prodWebhooksSecret|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopAccountSettings[prodWebhooksSecret]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Prod Webhooks Secret -->
        </div>
        <!-- Webhooks URL -->
        <div class="form-group worldlineop-webhooks-block">
          <label class="control-label col-lg-3">
            <span>{l s='Webhooks URL' mod='worldlineop'}</span>
          </label>
          <div class="col-lg-9">
            <div class="form-control-static">
              <code id="js-webhooks-code">{$data.extra.path.controllers.webhooks|escape:'htmlall':'UTF-8'}</code>
              <i class="icon icon-copy js-icon-copy"></i>
            </div>
          </div>
          <div class="col-lg-offset-3 col-lg-9">
            <div class="help-block">
              <p>{l s='To avoid copy/paste issue, use the "copy" icon to copy the URL' mod='worldlineop'}</p>
            </div>
          </div>
        </div>
        <!-- /Webhooks URL -->
        <input type="hidden" name="action" value="saveAccountForm"/>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right" name="submitSaveAccountForm">
        <i class="process-icon-save"></i> {l s='Save' mod='worldlineop'}
      </button>
      <button type="submit" class="btn btn-default pull-right" name="submitTestCredentialsForm">
        <i class="process-icon-ok"></i> {l s='Save & Check credentials' mod='worldlineop'}
      </button>
    </div>
  </form>
</div>

{literal}
<script type="text/javascript">
  function copyInput($input) {
    let range = document.createRange();
    let sel = window.getSelection();

    range.setStartBefore($input.firstChild);
    range.setEndAfter($input.lastChild);
    sel.removeAllRanges();
    sel.addRange(range);

    try {
      document.execCommand('copy');
      showSuccessMessage(copyMessage);
    } catch (err) {
      console.error('Unable to copy');
    }
  }

  $('.js-icon-copy').on('click', function (e) {
    copyInput(document.getElementById('js-webhooks-code'));
  });
</script>
{/literal}
