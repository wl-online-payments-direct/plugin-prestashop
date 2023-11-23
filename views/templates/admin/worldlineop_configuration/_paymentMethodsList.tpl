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

{if empty($data['paymentMethodsSettings'][$name|escape:'html':'UTF-8'])}
  <div class="alert alert-info">
    {l s='You do not have any payment methods. Please refresh the list to sync payment options' mod='worldlineop'}
  </div>
{/if}

<div class="col-lg-offset-3">
  <div class="payment-products">
    {assign var="i" value=0}
    {foreach $data['paymentMethodsSettings'][$name|escape:'html':'UTF-8'] as $paymentMethod}
      <div class="payment-product panel">
        <div class="logo">
          <img src="{$paymentMethod.logo|escape:'html':'UTF-8'}"/>
        </div>
        <p class="title">{$paymentMethod.identifier|escape:'html':'UTF-8'}</p>
        {if 'iframePaymentMethods' !== $name}
          <span class="enable-title">{l s='Enable' mod='worldlineop'}</span>
          <span class="switch prestashop-switch fixed-width-md">
            <input type="radio"
                   value="1"
                   name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][enabled]"
                   id="worldlineopPaymentMethodsSettings_{$type|escape:'html':'UTF-8'}_product_{$paymentMethod.productId|intval}_enabled_on"
                   {if $paymentMethod.enabled === true}checked="checked"{/if}>
            <label for="worldlineopPaymentMethodsSettings_{$type|escape:'html':'UTF-8'}_product_{$paymentMethod.productId|intval}_enabled_on">
              {l s='Yes' mod='worldlineop'}
            </label>
            <input type="radio"
                   value="0"
                   name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][enabled]"
                   id="worldlineopPaymentMethodsSettings_{$type|escape:'html':'UTF-8'}_product_{$paymentMethod.productId|intval}_enabled_off"
                   {if $paymentMethod.enabled != true}checked="checked"{/if}>
            <label for="worldlineopPaymentMethodsSettings_{$type|escape:'html':'UTF-8'}_product_{$paymentMethod.productId|intval}_enabled_off">
              {l s='No' mod='worldlineop'}
            </label>
            <a class="slide-button btn"></a>
          </span>
        {else}
          <input type="hidden"
                 name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][enabled]"
                 value="1"/>
        {/if}
        <input type="hidden"
               name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][logo]"
               value="{$paymentMethod.logo|escape:'html':'UTF-8'}"/>
        <input type="hidden"
               name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][type]"
               value="{$paymentMethod.type|escape:'html':'UTF-8'}"/>
        <input type="hidden"
               name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][productId]"
               value="{$paymentMethod.productId|intval}"/>
        <input type="hidden"
               name="worldlineopPaymentMethodsSettings[{$name|escape:'html':'UTF-8'}][{$i|intval}][identifier]"
               value="{$paymentMethod.identifier|escape:'html':'UTF-8'}"/>
      </div>
      {$i = $i + 1}
    {/foreach}
  </div>
</div>
