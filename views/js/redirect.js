/*
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop partner
 * @copyright 2021 Worldline Online Payments
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

async function worldlineopRedirect() {
  const controller = worldlineopRedirectController.replace(/\amp;/g, '');

  return new Promise(function (resolve, reject) {
    const form = new FormData();

    form.append('ajax', true);
    form.append('RETURNMAC', returnMac);
    form.append('hostedCheckoutId', hostedCheckoutId);
    form.append('paymentId', paymentId);
    form.append('token', worldlineopCustomerToken);

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

function setTimer(callback, delay, iterations) {
  var i = 0;
  var intervalID = window.setInterval(function () {
    callback();

    if (++i === iterations) {
      window.clearInterval(intervalID);
      document.getElementById('js-worldlineop-timeout-message').style.display = 'block';
      document.getElementById('js-worldlineop-loader').style.display = 'none';
    }
  }, delay);
}

setTimer(function () {
  worldlineopRedirect().then((result) => {
    if (result.redirectUrl) {
      window.top.location.href = result.redirectUrl;
    }
  }).catch(() => {

  });
}, 3000, 14);
