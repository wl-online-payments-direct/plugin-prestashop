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

{if $tokenSurcharge}
  <div class="alert alert-info">
    <p>{l s='Please note that a surcharge will be applied to the total amount:' mod='worldlineop'}</p>
    <ul>
      <li>
        {l s='Initial total:' mod='worldlineop'}
        {$tokenSurcharge.amountWithoutSurcharge} {$tokenSurcharge.currencyIso}
      </li>
      <li>
        {l s='Surcharge amount:' mod='worldlineop'}
        {$tokenSurcharge.surchargeAmount} {$tokenSurcharge.currencyIso}
      </li>
      <li>
        <b>
          {l s='Total amount with surcharge:' mod='worldlineop'}
          {$tokenSurcharge.amountWithSurcharge} {$tokenSurcharge.currencyIso}
        </b>
      </li>
    </ul>
  </div>
{/if}

<div class="js-worldlineop-1click-container-{$tokenId}">
  <div id="js-worldlineop-1click-{$tokenId}" class="js-worldlineop-htp worldlineop-htp"></div>

  <div class="js-worldlineop-generic-error alert alert-danger" style="display: none">
    {l s='An error occurred while processing the payment.' mod='worldlineop'}
    <a href="javascript:window.location.reload()">{l s='Please click here' mod='worldlineop'}</a>
    {l s='to refresh this page or contact our Customer Service' mod='worldlineop'}
  </div>
  <div class="js-worldlineop-error alert alert-danger" style="display: none">
    <span></span>
    <a href="javascript:window.location.reload()">{l s='Please click here' mod='worldlineop'}</a>
    {l s='to refresh this page or contact our Customer Service' mod='worldlineop'}
  </div>
</div>

<script type="text/javascript">
  hostedTokenizationObj = new htpPrototype(document);

  hostedTokenizationObj.elems = {
    iframeContainer: document.querySelector('.js-worldlineop-1click-container-{$tokenId}'),
    payBtnId: 'js-worldlineop-token-btn-submit-{$tokenId}',
  };
  hostedTokenizationObj.urls = {
    htp: "{$hostedTokenizationPageUrl|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}",
    paymentController: "{$createPaymentUrl|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}",
  };
  hostedTokenizationObj.dynamicSurcharge = false;
  hostedTokenizationObj.cartDetails = {
    totalCents: "{$totalCartCents|intval}",
    currencyCode: "{$cartCurrencyCode|escape:'javascript':'UTF-8'}",
    customerToken: "{$worldlineopCustomerToken|escape:'javascript':'UTF-8'}",
    cardToken: "{$cardToken|escape:'javascript':'UTF-8'}",
  };
  hostedTokenizationObj.init();
</script>
