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

<div class="panel">
  <div class="row">
    <div class="worldlineop-header flex col-xs-12">
      <div class="worldlineop-logo">
        <img src="{$data.extra.path.img|escape:'html':'UTF-8'}worldline-horizontal.png"/>
      </div>
      <div class="worldlineop-support flex">
        <div class="contact flex">
          <i class="icon icon-question-circle icon-big flex"></i>
          <div class="flex">
            <p><b>{l s='Do you have a question?' mod='worldlineop'}</b></p>
            <p>{l s='Contact us using' mod='worldlineop'}
              <a target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product=86428">
                {l s='this link' mod='worldlineop'}
              </a>
            </p>
          </div>
        </div>
        <div class="cta-buttons flex">
          <a class="btn btn-primary" href="{$data.extra.path.module|escape:'html':'UTF-8'}readme_en.pdf">
            <i class="icon icon-book"></i>
            {l s='Download User guide' mod='worldlineop'}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
