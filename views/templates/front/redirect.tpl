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

{extends file='page.tpl'}

{block name='page_content_container'}
  <div id="js-worldlineop-loader">
    <h1>{l s='Please wait while we are processing your payment' mod='worldlineop'}</h1>
    <img src="{$img_path}icons/loader.svg" title="Loading..." alt="Loading..." />
  </div>
  <div id="js-worldlineop-timeout-message" style="display: none;">
    <div class="alert alert-warning">
      <p>{l s='The transaction has not been confirmed yet.' mod='worldlineop'}</p>
      <p>
        {l s='We suggest you contact our customer service using this link:' mod='worldlineop'}
        <a title="{l s='Contact-us' mod='worldlineop'}" href="{$link->getPageLink('contact', true)}">
          {$link->getPageLink('contact', true)}
        </a>
      </p>
      {if $hostedCheckoutId || $paymentId}
        <p>
          {l s='Please also provide us these transactions details:' mod='worldlineop'}<br>
          {if $paymentId}
            <b>{l s='Payment ID:' mod='worldlineop'}</b> {$paymentId}
          {/if}
          {if $hostedCheckoutId}
            <b>{l s='Checkout ID:' mod='worldlineop'}</b> {$hostedCheckoutId}
          {/if}
        </p>
      {/if}
    </div>
  </div>
{/block}

{block name="javascript_bottom"}
  {$smarty.block.parent}
  <script>
    const worldlineopRedirectController = "{$worldlineopRedirectController|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
    const returnMac = "{$returnMac}";
    const hostedCheckoutId = "{$hostedCheckoutId}";
    const paymentId = "{$paymentId}";
    const worldlineopCustomerToken = "{$worldlineopCustomerToken}";
  </script>
{/block}
