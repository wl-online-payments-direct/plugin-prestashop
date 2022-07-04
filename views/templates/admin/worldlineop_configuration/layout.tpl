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

<div class="row">
  <div class="col-lg-10 col-lg-offset-1">
    <div id="worldlineop-configuration">
      <div class="worldlineop-information">
        <i class="icon icon-info-circle"></i>
        {l s='Worldline Online Payments module' mod='worldlineop'} v{$data.extra.moduleVersion|escape:'html':'UTF-8'} -
        <a data-toggle="modal"
           data-target="#worldlineop-modal-whatsnew"
           href="#">
          {l s='What\'s new?' mod='worldlineop'}
        </a>
      </div>
      {include file="./_header.tpl"}
      <div class="form-wrapper">
        <ul class="nav nav-tabs">
          <li {if $data.activeTab == 'account'}class="active"{/if}>
            <a href="#account" data-toggle="tab">
              <i class="icon icon-user"></i>
              {l s='My account' mod='worldlineop'}
            </a>
          </li>
          <li class="js-tab-advanced{if $data.activeTab == 'advancedSettings'} active{/if}">
            <a href="#advanced-settings" data-toggle="tab">
              <i class="icon icon-cogs"></i>
              {l s='Advanced Settings' mod='worldlineop'}
            </a>
          </li>
          <li class="js-tab-advanced{if $data.activeTab == 'paymentMethods'} active{/if}">
            <a href="#payment-methods" data-toggle="tab">
              <i class="icon icon-credit-card"></i>
              {l s='Payment Methods' mod='worldlineop'}
            </a>
          </li>
          <li class="js-worldlineop-advanced-settings-block worldlineop-advanced-settings-block">
            <div class="js-worldlineop-advanced-settings-switch">
              {l s='Show advanced settings' mod='worldlineop'}
              <span class="switch prestashop-switch fixed-width-sm">
                <input type="radio"
                       value="1"
                       name="worldlineopAdvancedSettings[advancedSettingsEnabled]"
                       id="worldlineopAdvancedSettings_advancedSettingsEnabled_on"
                       {if $data.extra.advancedSettingsEnabled === 'true'}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_advancedSettingsEnabled_on">{l s='Yes' mod='worldlineop'}</label>
                <input type="radio"
                       value="0"
                       name="worldlineopAdvancedSettings[advancedSettingsEnabled]"
                       id="worldlineopAdvancedSettings_advancedSettingsEnabled_off"
                       {if $data.extra.advancedSettingsEnabled != 'true'}checked="checked"{/if}>
                <label for="worldlineopAdvancedSettings_advancedSettingsEnabled_off">{l s='No' mod='worldlineop'}</label>
                <a class="slide-button btn"></a>
              </span>
            </div>
          </li>
        </ul>
        <div class="tab-content panel">
          <div id="account" class="tab-pane {if $data.activeTab == 'account'}active{/if}">
            {include file="./_account.tpl"}
          </div>
          <div id="advanced-settings" class="tab-pane {if $data.activeTab == 'advancedSettings'}active{/if}">
            {include file="./_advancedSettings.tpl"}
          </div>
          <div id="payment-methods" class="tab-pane {if $data.activeTab == 'paymentMethods'}active{/if}">
            {include file="./_paymentMethodsSettings.tpl"}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  var languages = new Array();

  // Multilang field setup must happen before document is ready so that calls to displayFlags() to avoid
  // precedence conflicts with other document.ready() blocks
  {foreach $languages as $k => $language}
  languages[{$k}] = {
    id_lang: {$language.id_lang|escape:'javascript':'UTF-8'},
    iso_code: '{$language.iso_code|escape:'javascript':'UTF-8'}',
    name: '{$language.name|escape:'javascript':'UTF-8'}',
    is_default: '{$language.is_default|escape:'javascript':'UTF-8'}'
  };
  {/foreach}
</script>
