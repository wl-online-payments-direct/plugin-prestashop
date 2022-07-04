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

<div id="worldlineop-admin-order-container">
  {$html}
</div>
{literal}
  <script type="text/javascript">
    const worldlineopAdminOrderContainer = document.querySelector('#worldlineop-admin-order-container');
    const refundForm = document.querySelector('#worldlineop-refund-form');

    worldlineopAdminOrderContainer.addEventListener('click', function (e) {
      if (e.target.matches('#worldlineop-btn-capture') ||
        e.target.matches('#worldlineop-btn-refund') ||
        e.target.matches('#worldlineop-btn-cancel')
      ) {
        e.preventDefault();

        var formToSubmit;
        if (e.target.matches('#worldlineop-btn-capture')) {
          if (!window.confirm(alertCapture)) {
            return false;
          }

          formToSubmit = document.querySelector('#worldlineop-capture-form');
        } else if (e.target.matches('#worldlineop-btn-refund')) {
          if (!window.confirm(alertRefund)) {
            return false;
          }

          formToSubmit = document.querySelector('#worldlineop-refund-form');
        } else {
          if (!window.confirm(alertCancel)) {
            return false;
          }

          formToSubmit = document.querySelector('#worldlineop-cancel-form');
        }

        const submitBtn = formToSubmit.querySelector('button');

        submitBtn.disabled = true;
        worldlineopAdminOrderContainer.style.opacity = 0.6;
        worldlineopPostTransaction(formToSubmit).then((result) => {
          worldlineopAdminOrderContainer.innerHTML = result.result_html;
        }).catch(() => {
        }).finally(() => {
          worldlineopAdminOrderContainer.style.opacity = 1;
          submitBtn.disabled = false;
        });
      }
    }, false);

    async function worldlineopPostTransaction(formSent) {
      const controller = worldlineopAjaxTransactionUrl.replace(/\amp;/g, '');

      return new Promise(function (resolve, reject) {
        const form = new FormData(formSent);

        fetch(controller, {
          body: form,
          method: 'post',
        }).then((response) => {
          resolve(response.json());
        }).catch((err) => {
          reject(err);
        });
      });
    }
  </script>
{/literal}
