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

<div class="row">
  <div class="col-md-12">
    <h2>{l s='Gift card specific configuration' mod='worldlineop'}</h2>
    <p class="subtitle">{l s='Please configure this section in case you accept gift cards as payment methods with Worldline Online Payments' mod='worldlineop'}</p>
  </div>
</div>
<div class="row form-group">
  <div class="col-md-8">
    <label for="worldlineopGiftCard[type]" class="form-control-label">{l s='Product type' mod='worldlineop'}</label>
    <select id="worldlineopGiftCard[type]"
            name="worldlineopGiftCard[type]"
            class="custom-select custom-select">
      <option {if $worldlineopGCSelectedType == $worldlineopGCTypeNone}selected="selected"{/if} value="{$worldlineopGCTypeNone|escape:'htmlall':'UTF-8'}">{l s='None' mod='worldlineop'}</option>
      <option {if $worldlineopGCSelectedType == $worldlineopGCTypeFoodDrink}selected="selected"{/if} value="{$worldlineopGCTypeFoodDrink|escape:'htmlall':'UTF-8'}">{l s='Food & Drink' mod='worldlineop'}</option>
      <option {if $worldlineopGCSelectedType == $worldlineopGCTypeHomeGarden}selected="selected"{/if} value="{$worldlineopGCTypeHomeGarden|escape:'htmlall':'UTF-8'}">{l s='Home & Garden' mod='worldlineop'}</option>
      <option {if $worldlineopGCSelectedType == $worldlineopGCTypeGiftFlowers}selected="selected"{/if} value="{$worldlineopGCTypeGiftFlowers|escape:'htmlall':'UTF-8'}">{l s='Gift & Flowers' mod='worldlineop'}</option>
    </select>
    <input type="hidden" name="worldlineopGiftCard[form]" value="1" />
  </div>
</div>
