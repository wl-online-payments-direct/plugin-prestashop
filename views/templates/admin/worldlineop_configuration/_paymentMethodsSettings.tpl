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

<div class="panel js-worldlineop-payment-methods-settings-form">
  <form class="form-horizontal"
        action="#"
        name="worldlineop_payment_methods_settings_form"
        id="worldlineop-payment-methods-settings-form"
        method="post"
        enctype="multipart/form-data">
    <div class="row">
      <div class="worldlineop-payment-methods-settings col-xs-12">
        <div class="alert alert-info">
          <p>
            {l s='In this section, you can customize the display of your payment methods to pay in:' mod='worldlineop'}
            <ul>
              <li><b>{l s='Redirect mode' mod='worldlineop'}</b> {l s='(All payment methods) Customers will complete the PAYMENT ON REDIRECTION to a Worldline Hosted Page' mod='worldlineop'}</li>
              <li><b>{l s='One page checkout' mod='worldlineop'}</b> {l s='(Cards only) Customers will complete the PAYMENT ON YOUR WEBSITE itself with an embedded iFrame (no redirection)' mod='worldlineop'}</li>
            </ul>
          </p>
          <p>
            {l s='Please note that you can fully customize the payment page by setting the name of a template you created previously in the File Manager, on the Worldline portal.' mod='worldlineop'}
          </p>
        </div>

        <h3 class="title">{l s='Redirect Mode (All Payment Methods)' mod='worldlineop'}</h3>
        <!-- Display Generic Button -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            <span>
              {l s='Payment method selection after redirect' mod='worldlineop'}<br>
            </span>
          </label>
          <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-xl">
              <input type="radio"
                     value="1"
                     name="worldlineopPaymentMethodsSettings[displayGenericOption]"
                     id="worldlineopPaymentMethodsSettings_displayGenericOption_on"
                     {if $data.paymentMethodsSettings.displayGenericOption === true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayGenericOption_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopPaymentMethodsSettings[displayGenericOption]"
                     id="worldlineopPaymentMethodsSettings_displayGenericOption_off"
                     {if $data.paymentMethodsSettings.displayGenericOption != true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayGenericOption_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='A unique pay button to be redirected to pay on Worldline hosted page' mod='worldlineop'}
            </div>
          </div>
        </div>
        <!-- /Display Generic Button -->
        <!-- Generic Logo -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
              <span>
                {l s='Generic logo displayed on your payment page' mod='worldlineop'}
              </span>
          </label>
          <div class="col-lg-9">
              {if $data.paymentMethodsSettings.genericLogoFilename}
                <img class="preview-logo"
                     src="{$data.extra.path.img|escape:'html':'UTF-8'}payment_logos/{$data.paymentMethodsSettings.genericLogoFilename|escape:'html':'UTF-8'}"/>
              {/if}
            <input type="file"
                   name="worldlineopPaymentMethodsSettings[genericLogo]"
                   id="worldlineopPaymentMethodsSettings[genericLogo]"
                   class="worldlineop-upload js-worldlineop-upload"/>
            <label for="worldlineopPaymentMethodsSettings[genericLogo]">
              <i class="icon icon-upload"></i>
              <span>
                    {l s='Upload' mod='worldlineop'}
                  </span>
            </label>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
              {if $data.paymentMethodsSettings.genericLogoFilename}
                <input type="checkbox" id="worldlineopPaymentMethodsSettings[deleteGenericLogo]" name="worldlineopPaymentMethodsSettings[deleteGenericLogo]" />
                <label for="worldlineopPaymentMethodsSettings[deleteGenericLogo]">{l s='Delete current logo' mod='worldlineop'}</label>
              {/if}
            <div class="help-block">
                {l s='You can upload here a new logo (file types accepted for logos are: .png .gif .jpg only)' mod='worldlineop'}<br/>
                {l s='We recommend that you use images with 20px height & 120px length maximum' mod='worldlineop'}
              <span></span>
            </div>
          </div>
        </div>
        <!-- /Generic Logo -->
        <!-- Redirect CTA -->
        <div class="form-group">
          <label class="control-label col-lg-3 ">
            <span>
              {l s='Pay button title' mod='worldlineop'}
            </span>
          </label>
          <div class="col-lg-9">
            {foreach from=$languages item=language}
              <div class="translatable-field flex lang-{$language.id_lang|intval}" {if $language.iso_code != $lang_iso}style="display:none;"{/if}>
                <div class="col-lg-5">
                  <input type="text"
                         id="worldlineop-redirect-cta-{$language.id_lang|intval}"
                         name="worldlineopPaymentMethodsSettings[redirectCallToAction][{$language.iso_code|escape:'html':'UTF-8'}]"
                         class=""
                         value="{if isset($data['paymentMethodsSettings']['redirectCallToAction'][$language.iso_code])}{$data['paymentMethodsSettings']['redirectCallToAction'][$language.iso_code|escape:'html':'UTF-8']}{/if}">
                </div>
                <div class="col-lg-2">
                  <button type="button"
                          class="btn btn-default dropdown-toggle"
                          tabindex="-1"
                          data-toggle="dropdown">
                    {$language.iso_code|escape:'html':'UTF-8'}
                    <i class="icon-caret-down"></i>
                  </button>
                  <ul class="dropdown-menu">
                    {foreach from=$languages item=language}
                      <li>
                        <a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">
                          {$language.name|escape:'html':'UTF-8'}
                        </a>
                      </li>
                    {/foreach}
                  </ul>
                </div>
              </div>
            {/foreach}
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='Title of the payment selection button on your checkout page' mod='worldlineop'}
              <span></span>
            </div>
          </div>
        </div>
        <!-- /Redirect CTA -->
        <!-- Display Payment Options -->
        <div class="form-group js-worldlineop-display-redirect-pm-block">
          <label class="control-label col-lg-3">
            <span>
              {l s='Payment method selection before redirect' mod='worldlineop'}<br>
            </span>
          </label>
          <div class="col-lg-9 js-worldlineop-display-redirect-pm-switch">
            <span class="switch prestashop-switch fixed-width-xl">
              <input type="radio"
                     value="1"
                     name="worldlineopPaymentMethodsSettings[displayRedirectPaymentOptions]"
                     id="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on"
                     {if $data.paymentMethodsSettings.displayRedirectPaymentOptions === true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopPaymentMethodsSettings[displayRedirectPaymentOptions]"
                     id="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_off"
                     {if $data.paymentMethodsSettings.displayRedirectPaymentOptions != true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='Each payment method identified by a button. On click, customer is redirected to pay on Worldline Online Payments page' mod='worldlineop'}
            </div>
          </div>
        </div>
        <!-- /Display Payment Options -->
        <!-- Redirect payment methods list -->
        <div class="js-worldlineop-redirect-payment-methods-block">
          <div class="row">
            <div class="col-lg-offset-3 col-lg-9">
              <button class="btn btn-default js-worldlineop-refresh-redirect-pm-btn">
                <i class="icon icon-refresh"></i>
                {l s='Refresh list of available payment methods' mod='worldlineop'}
              </button>
            </div>
          </div>
          <div id="js-worldlineop-redirect-payment-methods-list" class="worldlineop-payment-methods-list">
            {include file="./_paymentMethodsList.tpl" type="redirect" name="redirectPaymentMethods"}
          </div>
        </div>
        <!-- /Redirect payment methods list -->
        <!-- Template filename -->
        <div class="form-group">
          <label class="control-label col-lg-3">
            <span>{l s='Template filename for redirect payment' mod='worldlineop'}</span>
          </label>
          <div class="col-lg-9">
            <div class="fixed-width-xxl">
              <input value="{$data.paymentMethodsSettings.redirectTemplateFilename|escape:'htmlall':'UTF-8'}"
                     type="text"
                     name="worldlineopPaymentMethodsSettings[redirectTemplateFilename]"
                     class="input fixed-width-xxl">
            </div>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='If you are using a customized template, please enter the name here. If empty, the standard payment page will be displayed.' mod='worldlineop'}<br>
              {l s='Payment page look and feel can be customized on Worldline Back Office.' mod='worldlineop'}
            </div>
          </div>
        </div>
        <!-- /Template filename -->


        <h3 class="title">{l s='One Page Checkout Mode (Cards only)' mod='worldlineop'}</h3>
        <!-- Display Payment Options -->
        <div class="form-group js-worldlineop-display-iframe-pm-block">
          <label class="control-label col-lg-3">
            <span>
              {l s='Accept cards payments on iframe' mod='worldlineop'}<br>
            </span>
          </label>
          <div class="col-lg-9 js-worldlineop-display-iframe-pm-switch">
            <span class="switch prestashop-switch fixed-width-xl">
              <input type="radio"
                     value="1"
                     name="worldlineopPaymentMethodsSettings[displayIframePaymentOptions]"
                     id="worldlineopPaymentMethodsSettings_displayIframePaymentOptions_on"
                     {if $data.paymentMethodsSettings.displayIframePaymentOptions === true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayIframePaymentOptions_on">{l s='Yes' mod='worldlineop'}</label>
              <input type="radio"
                     value="0"
                     name="worldlineopPaymentMethodsSettings[displayIframePaymentOptions]"
                     id="worldlineopPaymentMethodsSettings_displayIframePaymentOptions_off"
                     {if $data.paymentMethodsSettings.displayIframePaymentOptions != true}checked="checked"{/if}>
              <label for="worldlineopPaymentMethodsSettings_displayIframePaymentOptions_off">{l s='No' mod='worldlineop'}</label>
              <a class="slide-button btn"></a>
            </span>
          </div>
          <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
              {l s='By activating this mode, your customers can pay with card on your checkout page itself without any redirection. ' mod='worldlineop'}<br>
              {l s='For all other alternate payment methods please select one of the redirection options above.' mod='worldlineop'}
            </div>
          </div>
        </div>
        <!-- /Display Payment Options -->
        <div class="js-worldlineop-iframe-payment-methods-block">
          <!-- Iframe CTA -->
          <div class="form-group">
            <label class="control-label col-lg-3 ">
              <span>
                {l s='Pay button title' mod='worldlineop'}
              </span>
            </label>
            <div class="col-lg-9">
              {foreach from=$languages item=language}
                <div class="translatable-field flex lang-{$language.id_lang|intval}" {if $language.iso_code != $lang_iso}style="display:none;"{/if}>
                  <div class="col-lg-5">
                    <input type="text"
                           id="worldlineop-iframe-cta-{$language.id_lang|intval}"
                           name="worldlineopPaymentMethodsSettings[iframeCallToAction][{$language.iso_code|escape:'html':'UTF-8'}]"
                           class=""
                           value="{if isset($data['paymentMethodsSettings']['iframeCallToAction'][$language.iso_code])}{$data['paymentMethodsSettings']['iframeCallToAction'][$language.iso_code|escape:'html':'UTF-8']}{/if}">
                  </div>
                  <div class="col-lg-2">
                    <button type="button"
                            class="btn btn-default dropdown-toggle"
                            tabindex="-1"
                            data-toggle="dropdown">
                      {$language.iso_code|escape:'html':'UTF-8'}
                      <i class="icon-caret-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                      {foreach from=$languages item=language}
                        <li>
                          <a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">
                            {$language.name|escape:'html':'UTF-8'}
                          </a>
                        </li>
                      {/foreach}
                    </ul>
                  </div>
                </div>
              {/foreach}
            </div>
            <div class="col-lg-9 col-lg-offset-3">
              <div class="help-block">
                {l s='Title of the payment selection button on your checkout page' mod='worldlineop'}
                <span></span>
              </div>
            </div>
          </div>
          <!-- /Iframe CTA -->
          <!-- Logo -->
          <div class="form-group">
            <label class="control-label col-lg-3 ">
              <span>
                {l s='Logo displayed on your payment page' mod='worldlineop'}
              </span>
            </label>
            <div class="col-lg-9">
              {if $data.paymentMethodsSettings.iframeLogoFilename}
                <img class="preview-logo"
                     src="{$data.extra.path.img|escape:'html':'UTF-8'}payment_logos/{$data.paymentMethodsSettings.iframeLogoFilename|escape:'html':'UTF-8'}"/>
              {/if}
              <input type="file"
                     name="worldlineopPaymentMethodsSettings[iframeLogo]"
                     id="worldlineopPaymentMethodsSettings[iframeLogo]"
                     class="worldlineop-upload js-worldlineop-upload"/>
              <label for="worldlineopPaymentMethodsSettings[iframeLogo]">
                <i class="icon icon-upload"></i>
                <span>
                    {l s='Upload' mod='worldlineop'}
                  </span>
              </label>
            </div>
            <div class="col-lg-9 col-lg-offset-3">
              {if $data.paymentMethodsSettings.iframeLogoFilename}
                <input type="checkbox" id="worldlineopPaymentMethodsSettings[deleteLogo]" name="worldlineopPaymentMethodsSettings[deleteLogo]" />
                <label for="worldlineopPaymentMethodsSettings[deleteLogo]">{l s='Delete current logo' mod='worldlineop'}</label>
              {/if}
              <div class="help-block">
                {l s='You can upload here a new logo (file types accepted for logos are: .png .gif .jpg only)' mod='worldlineop'}<br/>
                {l s='We recommend that you use images with 20px height & 120px length maximum' mod='worldlineop'}
                <span></span>
              </div>
            </div>
          </div>
          <!-- /Logo -->
          <!-- Iframe payment methods list -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Payment methods available' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <button class="btn btn-default js-worldlineop-refresh-iframe-pm-btn">
                <i class="icon icon-refresh"></i>
                {l s='Refresh list of available payment methods' mod='worldlineop'}
              </button>
            </div>
          </div>
          <div id="js-worldlineop-iframe-payment-methods-list" class="worldlineop-payment-methods-list">
            {include file="./_paymentMethodsList.tpl" type="iframe" name="iframePaymentMethods"}
          </div>
          <!-- /Iframe payment methods list -->
          <!-- Template filename -->
          <div class="form-group">
            <label class="control-label col-lg-3">
              <span>{l s='Template filename' mod='worldlineop'}</span>
            </label>
            <div class="col-lg-9">
              <div class="fixed-width-xxl">
                <input value="{$data.paymentMethodsSettings.iframeTemplateFilename|escape:'htmlall':'UTF-8'}"
                       type="text"
                       name="worldlineopPaymentMethodsSettings[iframeTemplateFilename]"
                       class="input fixed-width-xxl">
              </div>
            </div>
          </div>
          <!-- /Template filename -->
        </div>
        <input type="hidden" name="action" value="savePaymentMethodsSettingsForm"/>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right" name="submitPaymentMethodsSettingsForm">
        <i class="process-icon-save"></i> {l s='Save' mod='worldlineop'}
      </button>
    </div>
  </form>
</div>
