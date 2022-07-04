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

<table id="payment-tab" width="40%">
  <tr>
    <td class="payment center small grey bold" width="44%">{l s='Worldline Online Payment' mod='worldlineop'}</td>
    <td class="payment left white" width="56%">
      <table width="100%" border="0">
        <tr>
          <td class="center small">{l s='Reference' mod='worldlineop'}<br>{$worldlineop_transaction_id|escape:'htmlall':'UTF-8'}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
