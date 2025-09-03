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

<div id="worldlineop-admin-order" class="card">
  <h3 class="card-header">
    <img style="height: 22px;" src="{$settingsData.extra.path.img|escape:'htmlall':'UTF-8'}worldline-horizontal.png"/>
    {l s='Worldline Online Payments' mod='worldlineop'}
  </h3>
  <div class="card-body">
    {if isset($worldlineopAjaxTransactionError)}
      <div class="alert alert-danger">
        <p class="text-danger">{$worldlineopAjaxTransactionError|escape:'htmlall':'UTF-8'}</p>
      </div>
    {/if}
    {if isset($captureConfirmation) && $captureConfirmation}
      <div class="alert alert-success">
        <p class="text-success">{l s='Capture requested successfully' mod='worldlineop'}</p>
      </div>
    {/if}
    {if isset($refundConfirmation) && $refundConfirmation}
      <div class="alert alert-success">
        <p class="text-success">{l s='Refund requested successfully' mod='worldlineop'}</p>
      </div>
    {/if}
    {if isset($cancelConfirmation) && $cancelConfirmation}
      <div class="alert alert-success">
        <p class="text-success">{l s='Cancellation requested successfully' mod='worldlineop'}</p>
      </div>
    {/if}
    {foreach $transactionsData as $transactionData}
    <div class="row">
      <div class="col-md-12">
        <div class="info-block row-margin">
          <div class="row">
            <div class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Status' mod='worldlineop'}</strong></p>
              <strong id="">{$transactionData.payment.status|escape:'htmlall':'UTF-8'}</strong>
            </div>
            <div class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Transaction number' mod='worldlineop'}</strong></p>
              <strong id="">{$transactionData.payment.id|escape:'htmlall':'UTF-8'}</strong>
            </div>
            <div id="" class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Total' mod='worldlineop'}</strong></p>
              <strong id="">
                {$transactionData.payment.amount|escape:'htmlall':'UTF-8'}
                {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
              </strong>
              {if $transactionData.payment.hasSurcharge}
                <div>
                  <i>
                    {l s='(including' mod='worldlineop'}
                    {$transactionData.payment.surchargeAmount|escape:'htmlall':'UTF-8'} {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                    {l s='surcharge)' mod='worldlineop'}
                  </i>
                </div>
              {/if}
            </div>
            <div id="" class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Payment Method' mod='worldlineop'}</strong></p>
              <img src="{$settingsData.extra.path.img|escape:'htmlall':'UTF-8'}payment_logos/{$transactionData.payment.productId|intval}.svg"
                   style="height: 30px;"/>
            </div>
            <div id="" class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Fraud result' mod='worldlineop'}</strong></p>
              <strong id="">
                {$transactionData.payment.fraudResult|escape:'htmlall':'UTF-8'}
              </strong>
            </div>
            <div id="" class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Liability' mod='worldlineop'}</strong></p>
              <strong id="">
                {$transactionData.payment.liability|escape:'htmlall':'UTF-8'}
              </strong>
            </div>
            <div id="" class="col-sm text-center">
              <p class="text-muted mb-0"><strong>{l s='Exemption type' mod='worldlineop'}</strong></p>
              <strong id="">
                {$transactionData.payment.exemptionType|escape:'htmlall':'UTF-8'}
              </strong>
            </div>
          </div>
        </div>
      </div>
    </div>
    {/foreach}
    {if !empty($transactionData.payment.errors)}
      <div class="alert alert-danger">
        <ul>
          {foreach $transactionData.payment.errors as $error}
            <li><b>{l s='Error ID:' mod='worldlineop'}</b>{$error.id|escape:'htmlall':'UTF-8'} - <b>{l s='Code' mod='worldlineop'}</b> {$error.code|escape:'htmlall':'UTF-8'}</li>
          {/foreach}
        </ul>
      </div>
    {/if}
    {if $transactionData.payment.psOrderAmountMatch === false}
      <div class="alert alert-warning">
        <p>
          {l s='Warning: This order may not have been fully paid!' mod='worldlineop'}
        </p>
        <p>
          {l s='Please review the amounts in the section above and in the "Products" section in this page.' mod='worldlineop'}<br>
        </p>
      </div>
    {/if}
    <p></p>
    <div class="row">
      <div class="col-xl-6">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col">
                {if $transactionData.payment.hasSurcharge}
                  <h4>{l s='Surcharge details' mod='worldlineop'}</h4>
                  <div class="row mb-1">
                    <div class="col-6 text-right">{l s='Total amount without surcharge' mod='worldlineop'}</div>
                    <div class="col-6">
                      {$transactionData.payment.amountWithoutSurcharge|escape:'htmlall':'UTF-8'}
                      {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                    </div>
                  </div>
                  <div class="row mb-1">
                    <div class="col-6 text-right">{l s='Surcharge amount' mod='worldlineop'}</div>
                    <div class="col-6">
                      {$transactionData.payment.surchargeAmount|escape:'htmlall':'UTF-8'}
                      {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                    </div>
                  </div>
                  <div class="row mb-1">
                    <div class="col-6 text-right">{l s='Total amount with surcharge' mod='worldlineop'}</div>
                    <div class="col-6">
                      {$transactionData.payment.amount|escape:'htmlall':'UTF-8'}
                      {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                    </div>
                  </div>
                  <hr>
                {/if}
                <h4>{l s='Capture' mod='worldlineop'}</h4>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount captured' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.captures.totalCaptured|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount pending capture' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.captures.totalPendingCapture|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount that can be captured' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.captures.capturableAmount|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                {if $transactionData.actions.isAuthorized && $transactionData.captures.capturableAmount > 0}
                  <form class="form-horizontal"
                        action="{$link->getAdminLink('AdminWorldlineopAjaxTransaction')|escape:'htmlall':'UTF-8'}"
                        name="worldlineop_capture"
                        id="worldlineop-capture-form"
                        style="margin-top: 1.875rem"
                        method="post"
                        enctype="multipart/form-data">
                    <div class="form-group row">
                      <div class="col-sm">
                        <div class="input-group money-type">
                          <input type="text"
                                 id=""
                                 name="transaction[amountToCapture]"
                                 class="form-control"
                                 onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0"
                                 value="{$transactionData.captures.capturableAmount|escape:'htmlall':'UTF-8'}">
                          <div class="input-group-append">
                            <span class="input-group-text">{$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}</span>
                          </div>
                          <button id="worldlineop-btn-capture" class="btn btn-primary btn-sm ml-2">
                            {l s='Capture' mod='worldlineop'}
                          </button>
                        </div>
                        <input type="hidden" name="transaction[id]" value="{$transactionData.payment.id|escape:'htmlall':'UTF-8'}"/>
                        <input type="hidden" name="transaction[idOrder]" value="{$transactionData.orderId|intval}"/>
                        <input type="hidden" name="transaction[currencyCode]" value="{$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}"/>
                        <input type="hidden" name="action" value="capture"/>
                      </div>
                    </div>
                  </form>
                {/if}
                {if $transactionData.actions.isCancellable}
                  <hr>
                  <h4>{l s='Cancel transaction' mod='worldlineop'}</h4>
                  <div class="alert alert-warning">
                    <p class="alert-text">{l s='Be careful, this action cannot be reverted' mod='worldlineop'}</p>
                  </div>
                  <form class="form-horizontal"
                        action="{$link->getAdminLink('AdminWorldlineopAjaxTransaction')|escape:'htmlall':'UTF-8'}"
                        name="worldlineop_cancel"
                        id="worldlineop-cancel-form"
                        method="post"
                        enctype="multipart/form-data">
                    <div class="form-group row">
                      <div class="col-sm">
                        <button id="worldlineop-btn-cancel"  class="btn btn-danger">
                          {l s='Cancel' mod='worldlineop'}
                          {$transactionData.captures.capturableAmount|escape:'htmlall':'UTF-8'}
                          {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                        </button>
                      </div>
                    </div>
                    <input type="hidden" name="transaction[id]" value="{$transactionData.payment.id|escape:'htmlall':'UTF-8'}"/>
                    <input type="hidden" name="transaction[idOrder]" value="{$transactionData.orderId|intval}"/>
                    <input type="hidden" name="transaction[currencyCode]" value="{$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}"/>
                    <input type="hidden" name="action" value="cancel"/>
                  </form>
                {/if}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col">
                <h4>{l s='Refund' mod='worldlineop'}</h4>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount refunded' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.refunds.totalRefunded|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount pending refund' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.refunds.totalPendingRefund|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col-6 text-right">{l s='Amount that can be refunded' mod='worldlineop'}</div>
                  <div class="col-6">
                    {$transactionData.refunds.refundableAmount|escape:'htmlall':'UTF-8'}
                    {$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}
                  </div>
                </div>
                {if $transactionData.captures.capturableAmount > 0 && !$transactionData.actions.isRefundable}
                  <hr>
                  <div class="alert alert-info">
                    <p>
                      {l s='You can make refunds if the initial transaction is fully captured or partially cancelled' mod='worldlineop'}
                    </p>
                  </div>
                {/if}
                {if $transactionData.actions.isRefundable && $transactionData.refunds.refundableAmount > 0}
                  <hr>
                  <form class="form-horizontal"
                        action="{$link->getAdminLink('AdminWorldlineopAjaxTransaction')|escape:'htmlall':'UTF-8'}"
                        name="worldlineop_refund"
                        id="worldlineop-refund-form"
                        method="post"
                        enctype="multipart/form-data">
                    <div class="form-group row">
                      <div class="col-sm">
                        <div class="input-group money-type">
                          <input type="text"
                                 id=""
                                 name="transaction[amountToRefund]"
                                 class="form-control"
                                 onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0"
                                 value="{$transactionData.refunds.refundableAmount|escape:'htmlall':'UTF-8'}">
                          <div class="input-group-append">
                            <span class="input-group-text">{$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}</span>
                          </div>
                          <button id="worldlineop-btn-refund"  class="btn btn-primary btn-sm ml-2">
                            {l s='Make refund' mod='worldlineop'}
                          </button>
                        </div>
                        <input type="hidden" name="transaction[id]" value="{$transactionData.payment.id|escape:'htmlall':'UTF-8'}"/>
                        <input type="hidden" name="transaction[idOrder]" value="{$transactionData.orderId|intval}"/>
                        <input type="hidden" name="transaction[currencyCode]" value="{$transactionData.payment.currencyCode|escape:'htmlall':'UTF-8'}"/>
                        <input type="hidden" name="action" value="refund"/>
                      </div>
                    </div>
                  </form>
                {/if}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

