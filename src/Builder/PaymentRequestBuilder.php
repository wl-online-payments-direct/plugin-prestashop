<?php
/**
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

namespace WorldlineOP\PrestaShop\Builder;

use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\RedirectionData;
use OnlinePayments\Sdk\Domain\ThreeDSecure;

/**
 * Class PaymentRequestBuilder
 * @package WorldlineOP\PrestaShop\Builder
 */
class PaymentRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @return CardPaymentMethodSpecificInput
     */
    public function buildCardPaymentMethodSpecificInput()
    {
        $cardPaymentMethodSpecificInput = new CardPaymentMethodSpecificInput();
        $cardPaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );
        if (false !== $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setToken($this->tokenValue);
        }

        $threeDSecure = new ThreeDSecure();
        $redirectionData = new RedirectionData();

        $redirectionData->setReturnUrl(
            $this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectReturnIframe'])
        );
        $threeDSecure->setSkipAuthentication(false);
        $threeDSecure->setRedirectionData($redirectionData);
        $cardPaymentMethodSpecificInput->setThreeDSecure($threeDSecure);

        return $cardPaymentMethodSpecificInput;
    }

    /**
     * @return MobilePaymentMethodSpecificInput
     */
    public function buildMobilePaymentMethodSpecificInput()
    {
        $mobilePaymentMethodSpecificInput = new MobilePaymentMethodSpecificInput();
        $mobilePaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );

        return $mobilePaymentMethodSpecificInput;
    }

    /**
     * @return null
     */
    public function buildHostedCheckoutSpecificInput()
    {
        return null;
    }
}
